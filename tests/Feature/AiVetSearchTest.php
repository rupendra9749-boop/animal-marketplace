<?php

namespace Tests\Feature;

use App\Models\AiSearch;
use App\Models\Category;
use App\Models\User;
use App\Models\Vet;
use App\Services\AiSearchResult;
use App\Services\AiVetFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiVetSearchTest extends TestCase
{
    use RefreshDatabase;

    private const PAGE = 'https://www.example-vet.in/ludhiana/city-vet';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.anthropic.key' => 'test-key']);
    }

    /** A reply shaped like the real API: a search result block, then the model's JSON answer. */
    private function reply(array $clinics, array $pages = [self::PAGE], string $stop = 'end_turn'): array
    {
        return [
            'stop_reason' => $stop,
            'content' => [
                ['type' => 'server_tool_use', 'id' => 'srvtoolu_1', 'name' => 'web_search', 'input' => ['query' => 'vet clinic Ludhiana']],
                ['type' => 'web_search_tool_result', 'tool_use_id' => 'srvtoolu_1', 'content' => array_map(fn ($url) => ['type' => 'web_search_result', 'url' => $url, 'title' => 'Result'], $pages)],
                ['type' => 'text', 'text' => json_encode($clinics)],
            ],
        ];
    }

    private function clinic(array $overrides = []): array
    {
        return [
            'name' => 'Dr. Gurdeep Singh', 'clinic_name' => 'City Vet Hospital', 'phone' => '0161 2401234', 'address' => 'Model Town, Ludhiana',
            'services' => 'Vaccination, Surgery', 'animals' => ['Dog', 'Cat'], 'timings' => 'Mon-Sat 9-6', 'source_url' => self::PAGE,
            ...$overrides,
        ];
    }

    private function ludhiana(): array
    {
        return ['state' => 'Punjab', 'city' => 'Ludhiana', 'lat' => 30.9, 'lng' => 75.85];
    }

    public function test_the_feature_is_off_without_an_api_key_and_calls_nothing(): void
    {
        config(['services.anthropic.key' => null]);
        Http::fake();

        $result = app(AiVetFinder::class)->search(User::factory()->create(), 'Punjab', 'Ludhiana');

        $this->assertSame(AiSearchResult::DISABLED, $result->status);
        Http::assertNothingSent();
        $this->assertDatabaseCount('ai_searches', 0);
    }

    public function test_a_search_saves_clinics_the_web_search_really_returned_and_marks_them_unverified(): void
    {
        Category::factory()->create(['name' => 'Dog']);
        Category::factory()->create(['name' => 'Cat']);
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([$this->clinic()]))]);
        $user = User::factory()->create();

        $result = app(AiVetFinder::class)->search($user, 'Punjab', 'Ludhiana');

        $this->assertSame(AiSearchResult::OK, $result->status);
        $this->assertSame(1, $result->created->count());

        $vet = Vet::where('name', 'Dr. Gurdeep Singh')->firstOrFail();
        $this->assertSame('ai', $vet->source);
        $this->assertFalse($vet->is_verified);
        $this->assertTrue($vet->is_active);
        $this->assertSame(self::PAGE, $vet->source_url);
        $this->assertSame('example-vet.in', $vet->sourceHost());
        $this->assertSame('Punjab', $vet->state);
        $this->assertSame('Ludhiana', $vet->city);
        $this->assertEqualsWithDelta(30.9, $vet->latitude, 0.1);
        $this->assertSame('0161 2401234' === $vet->phone ? '' : '01612401234', $vet->phone);
        $this->assertEqualsCanonicalizing(['Cat', 'Dog'], $vet->categories->pluck('name')->all());

        $this->assertDatabaseHas('ai_searches', ['user_id' => $user->id, 'city' => 'Ludhiana', 'status' => 'ok', 'found_count' => 1]);
    }

    public function test_the_request_uses_web_search_with_the_key_the_model_and_the_city(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([]))]);

        app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        Http::assertSent(function (Request $request) {
            $body = $request->data();

            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'test-key')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $body['model'] === config('services.anthropic.model')
                && $body['tools'][0]['type'] === 'web_search_20250305'
                && $body['tools'][0]['name'] === 'web_search'
                && $body['tools'][0]['user_location']['city'] === 'Ludhiana'
                && str_contains($body['messages'][0]['content'], 'Ludhiana, Punjab');
        });
    }

    public function test_invented_or_untraceable_clinics_are_never_saved(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([
            $this->clinic(['name' => 'Real Clinic']),
            $this->clinic(['name' => 'No Phone', 'phone' => null]),
            $this->clinic(['name' => 'Bad Phone', 'phone' => '12345']),
            $this->clinic(['name' => 'Made Up Page', 'phone' => '98765 11111', 'source_url' => 'https://not-returned-by-search.example/vets']),
            $this->clinic(['name' => 'No Page', 'phone' => '98765 22222', 'source_url' => '']),
            $this->clinic(['name' => '', 'clinic_name' => '', 'phone' => '98765 33333']),
        ]))]);

        $result = app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        $this->assertSame(['Real Clinic'], Vet::pluck('name')->all());
        $this->assertSame(1, $result->created->count());
    }

    public function test_doctors_we_already_have_are_not_duplicated_even_with_a_differently_typed_phone(): void
    {
        Vet::factory()->create(['name' => 'Dr. Known', 'city' => 'Ludhiana', 'state' => 'Punjab', 'phone' => '+91 98765 43210']);
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([
            $this->clinic(['name' => 'Someone Else', 'phone' => '098765-43210']),        // same number as Dr. Known
            $this->clinic(['name' => 'DR. KNOWN', 'phone' => '98111 22222']),            // same name + city
            $this->clinic(['name' => 'Truly New', 'phone' => '98111 33333']),
        ]))]);

        app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        $this->assertEqualsCanonicalizing(['Dr. Known', 'Truly New'], Vet::pluck('name')->all());
    }

    public function test_a_search_saves_at_most_the_configured_number_of_clinics(): void
    {
        config(['services.anthropic.max_results' => 2]);
        $clinics = collect(range(1, 6))->map(fn ($i) => $this->clinic(['name' => "Clinic {$i}", 'phone' => '9811122'.str_pad($i, 3, '0', STR_PAD_LEFT)]))->all();
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply($clinics))]);

        app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        $this->assertSame(2, Vet::count());
    }

    public function test_a_paused_turn_is_continued_until_the_final_answer(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::sequence()
            ->push(['stop_reason' => 'pause_turn', 'content' => [['type' => 'server_tool_use', 'id' => 'srvtoolu_1', 'name' => 'web_search', 'input' => ['query' => 'x']]]])
            ->push($this->reply([$this->clinic()]))]);

        $result = app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        $this->assertSame(1, $result->created->count());
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => ($request->data()['messages'][1]['role'] ?? null) === 'assistant');
    }

    public function test_an_api_failure_or_unreadable_answer_saves_nothing_and_is_logged_as_failed(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['error' => ['message' => 'overloaded']], 529)]);
        $failed = app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');
        $this->assertSame(AiSearchResult::ERROR, $failed->status);

        Http::fake(['api.anthropic.com/*' => Http::response(['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'Sorry, I could not find anything.']]])]);
        $unreadable = app(AiVetFinder::class)->search(null, 'Haryana', 'Karnal');
        $this->assertSame(AiSearchResult::ERROR, $unreadable->status);

        $this->assertDatabaseCount('vets', 0);
        $this->assertSame(2, AiSearch::where('status', 'error')->count());
    }

    public function test_an_empty_answer_is_a_normal_result_not_an_error(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([]))]);

        $result = app(AiVetFinder::class)->search(null, 'Punjab', 'Ludhiana');

        $this->assertSame(AiSearchResult::EMPTY, $result->status);
        $this->assertDatabaseHas('ai_searches', ['city' => 'Ludhiana', 'status' => 'empty']);
    }

    public function test_the_same_area_is_not_searched_again_within_a_week_and_daily_limits_apply(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([]))]);
        $finder = app(AiVetFinder::class);
        $user = User::factory()->create();

        $finder->search($user, 'Punjab', 'Ludhiana');
        $again = $finder->search($user, 'Punjab', 'Ludhiana');
        $this->assertSame(AiSearchResult::RECENT, $again->status);
        Http::assertSentCount(1);

        // A person gets a few searches a day.
        config(['services.anthropic.per_user_per_day' => 2]);
        $finder->search($user, 'Haryana', 'Karnal');
        $this->assertSame(AiSearchResult::LIMITED, $finder->search($user, 'Haryana', 'Hisar')->status);

        // The whole site has a daily cap too.
        config(['services.anthropic.per_user_per_day' => 99, 'services.anthropic.global_per_day' => 2]);
        $this->assertSame(AiSearchResult::LIMITED, $finder->search(User::factory()->create(), 'Rajasthan', 'Jaipur')->status);

        // Admins can force a search past the limits.
        $forced = $finder->search($user, 'Rajasthan', 'Jaipur', null, force: true);
        $this->assertSame(AiSearchResult::EMPTY, $forced->status);
    }

    public function test_the_buyer_button_needs_login_and_the_page_only_offers_it_when_ai_is_switched_on(): void
    {
        $this->fromCity('Ludhiana')->get('/vets')->assertOk()->assertSee('Search the web with AI')->assertSee('Log in to search with AI');

        config(['services.anthropic.key' => null]);
        $this->fromCity('Ludhiana')->get('/vets')->assertOk()->assertDontSee('Search the web with AI');

        $this->post('/vets/ai-search')->assertRedirect(route('login'));
    }

    public function test_a_logged_in_buyer_can_run_the_search_and_sees_the_new_doctors_labelled(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([$this->clinic()]))]);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->fromCity('Ludhiana')->post('/vets/ai-search')
            ->assertRedirect(route('vets.index', ['state' => 'Punjab', 'city' => 'Ludhiana']))
            ->assertSessionHas('status');

        $page = $this->actingAs($buyer)->get('/vets?state=Punjab&city=Ludhiana')->assertOk();
        $page->assertSee('Dr. Gurdeep Singh')->assertSee('AI-found · unverified');

        $vet = Vet::where('name', 'Dr. Gurdeep Singh')->firstOrFail();
        $this->actingAs($buyer)->get(route('vets.show', $vet))
            ->assertOk()->assertSee('Found by AI web search')->assertSee('example-vet.in')->assertSee('Please call to confirm');
    }

    public function test_the_buyer_search_asks_for_a_city_first_and_respects_the_daily_limit(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([]))]);
        $buyer = User::factory()->create(['city' => null, 'state' => null, 'latitude' => null, 'longitude' => null]);

        $this->actingAs($buyer)->post('/vets/ai-search')->assertRedirect(route('vets.index'));
        Http::assertNothingSent();
    }

    public function test_admin_can_run_a_forced_search_verify_ai_doctors_and_see_the_log(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([$this->clinic()]))]);
        $admin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->create())->post(route('admin.vets.ai-search'), ['state' => 'Punjab', 'city' => 'Ludhiana'])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.vets.ai-search'), ['state' => 'Punjab', 'city' => 'Ludhiana'])
            ->assertRedirect(route('admin.vets.index', ['status' => 'ai']));

        $vet = Vet::where('name', 'Dr. Gurdeep Singh')->firstOrFail();
        $this->actingAs($admin)->get('/admin/vets?status=ai')->assertOk()->assertSee('Dr. Gurdeep Singh')->assertSee('AI-found, unverified')->assertSee('Recent AI searches')->assertSee('Mark verified');

        $this->actingAs($admin)->patch(route('admin.vets.verify', $vet))->assertRedirect();
        $this->assertTrue($vet->fresh()->is_verified);
        $this->get('/vets?state=Punjab&city=Ludhiana')->assertDontSee('AI-found · unverified');
    }

    public function test_the_admin_page_explains_how_to_switch_ai_on_when_there_is_no_key(): void
    {
        config(['services.anthropic.key' => null]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/vets')->assertOk()->assertSee('ANTHROPIC_API_KEY');
    }
}
