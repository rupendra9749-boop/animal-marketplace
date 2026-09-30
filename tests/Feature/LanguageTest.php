<?php

namespace Tests\Feature;

use App\Mail\NewOrderForSeller;
use App\Mail\OrderPlacedForBuyer;
use App\Models\Animal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @return array<string, string> */
    private function hindi(): array
    {
        return json_decode(file_get_contents(lang_path('hi.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function hi(string $english): string
    {
        return $this->hindi()[$english] ?? $this->fail("No Hindi translation for: {$english}");
    }

    public function test_english_is_the_default(): void
    {
        $this->get('/login')->assertOk()->assertSee('lang="en"', false)->assertSee('Log in');
    }

    public function test_only_english_and_hindi_are_offered(): void
    {
        $this->assertSame(['en', 'hi'], array_keys(config('app.locales')));

        $this->get('/language/fr')->assertNotFound();
        $this->get('/login')->assertSee('href="'.route('language.switch', 'hi').'"', false)->assertSee('हिन्दी');
    }

    public function test_switching_to_hindi_is_remembered_and_switching_back_works(): void
    {
        $this->get('/language/hi')->assertRedirect(route('home'))->assertCookie('locale', 'hi');

        $this->get('/login')->assertSee('lang="hi"', false)->assertSee($this->hi('Log in'));

        $this->get('/language/en')->assertRedirect();
        $this->get('/login')->assertSee('lang="en"', false)->assertSee('Log in');
    }

    public function test_switching_returns_to_the_page_the_person_was_on(): void
    {
        $this->from('/vets')->get('/language/hi')->assertRedirect('/vets');
    }

    public function test_the_choice_comes_back_from_the_cookie_in_a_new_session(): void
    {
        $this->withUnencryptedCookie('locale', 'hi')->get('/login')->assertSee('lang="hi"', false);
    }

    public function test_a_hindi_phone_starts_in_hindi_and_other_languages_fall_back_to_english(): void
    {
        $this->withHeader('Accept-Language', 'hi-IN,hi;q=0.9,en;q=0.5')->get('/login')->assertSee('lang="hi"', false);
        $this->withHeader('Accept-Language', 'en-IN,en;q=0.9,hi;q=0.5')->get('/login')->assertSee('lang="en"', false);
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get('/login')->assertSee('lang="en"', false);
    }

    public function test_an_explicit_choice_beats_the_phone_language(): void
    {
        $this->withHeader('Accept-Language', 'hi-IN')->get('/language/en');
        $this->withHeader('Accept-Language', 'hi-IN')->get('/login')->assertSee('lang="en"', false);
    }

    public function test_the_choice_is_saved_on_the_account_and_follows_the_person(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/language/hi');
        $this->assertSame('hi', $user->fresh()->locale);

        // A different phone: no session and no cookie, but the same account.
        $this->flushSession();
        $this->actingAs($user->fresh())->get('/account')->assertSee('lang="hi"', false);
    }

    public function test_people_who_sign_up_in_hindi_are_remembered_as_hindi_speakers(): void
    {
        $this->get('/language/hi');
        $this->post('/register', [
            'name' => 'Ramesh', 'email' => 'ramesh@example.com', 'phone' => '98765 43210',
            'state' => 'Punjab', 'city' => 'Ludhiana', 'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $this->assertSame('hi', User::where('email', 'ramesh@example.com')->firstOrFail()->locale);
    }

    public function test_messages_shown_after_an_action_are_in_the_chosen_language(): void
    {
        $animal = Animal::factory()->create(['stock' => 3]);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get('/language/hi');
        $this->actingAs($buyer)->post(route('cart.store', $animal), ['quantity' => 1])->assertSessionHas('status', $this->hi('Added to cart.'));
    }

    public function test_form_errors_are_in_hindi(): void
    {
        $this->get('/language/hi');

        $this->post('/register', [])->assertSessionHasErrors(['name', 'email']);
        $errors = session('errors')->get('name');
        $this->assertStringContainsString('ज़रूरी', $errors[0], 'the "required" message should be Hindi');
        $this->assertStringContainsString('नाम', $errors[0], 'the field name should be Hindi too');

        $this->post('/register', ['name' => 'X', 'email' => 'x@example.com', 'phone' => '12345', 'state' => 'Punjab', 'city' => 'Ludhiana', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors('phone');
        $this->assertSame($this->hi('Enter a valid 10-digit Indian mobile number.'), session('errors')->get('phone')[0]);
    }

    public function test_animal_types_genders_and_states_are_translated(): void
    {
        $hi = $this->hindi();
        foreach (['Cow', 'Buffalo', 'Goat', 'Dog', 'Cat', 'Bird', 'Sheep', 'Horse', 'Male', 'Female', 'Delhi', 'Punjab', 'Uttar Pradesh', 'Tamil Nadu'] as $word) {
            $this->assertArrayHasKey($word, $hi, "{$word} needs a Hindi name");
        }

        $this->get('/language/hi');
        $this->get('/register')->assertSee($hi['Punjab']);
    }

    public function test_order_emails_are_written_in_each_persons_own_language(): void
    {
        Mail::fake();
        $seller = User::factory()->seller()->create(['locale' => 'hi']);
        $buyer = User::factory()->create(['locale' => 'en']);
        $animal = Animal::factory()->create(['user_id' => $seller->id, 'price' => 1000, 'stock' => 5, 'listing_type' => 'sale']);

        $this->actingAs($buyer)->withSession(['cart' => [$animal->id => 1]])
            ->post('/checkout', ['shipping_name' => 'Ravi', 'shipping_address' => 'Farm road, Ludhiana'])->assertRedirect();

        Mail::assertSent(OrderPlacedForBuyer::class, fn ($m) => $m->locale === 'en');
        Mail::assertSent(NewOrderForSeller::class, fn ($m) => $m->locale === 'hi');
    }

    public function test_cities_come_with_a_hindi_spelling_for_the_hindi_picker(): void
    {
        $plain = $this->getJson('/locations/cities?state=Punjab')->assertOk()->json();
        $this->assertContains('Ludhiana', $plain, 'the English list is unchanged');

        $withHindi = $this->getJson('/locations/cities?state=Punjab&hi=1')->assertOk()->json();
        $this->assertContains(['Ludhiana', 'लुधियाना'], $withHindi);
        $this->assertCount(count($plain), $withHindi);
    }

    public function test_every_city_and_state_has_a_hindi_name(): void
    {
        $hindi = $this->hindi();
        $missing = [];
        foreach (json_decode(file_get_contents(resource_path('data/india-locations.json')), true)['states'] as $state) {
            isset($hindi[$state['name']]) || $missing[] = $state['name'];
            foreach ($state['cities'] as [$city]) {
                isset($hindi[$city]) || $missing[] = $city;
            }
        }

        $this->assertSame([], array_slice(array_unique($missing), 0, 20), 'places without a Hindi name');
    }

    public function test_place_names_show_in_hindi_on_the_pages(): void
    {
        $this->get('/language/hi');
        $vet = \App\Models\Vet::factory()->inCity('Ludhiana')->create(['is_active' => true]);

        $this->get('/vets?state=Punjab&city=Ludhiana')->assertOk()->assertSee('लुधियाना')->assertSee('पंजाब');
    }

    public function test_phone_numbers_can_be_typed_with_devanagari_digits(): void
    {
        $this->assertSame('+91 98765 43210', \App\Support\Phone::mobile('९८७६५ ४३२१०'));
        $this->assertSame('+91 98765 43210', \App\Support\Phone::mobile('+९१ 98765-43210'));
    }

    public function test_a_hindi_name_shows_its_first_letter_as_the_avatar(): void
    {
        $user = User::factory()->create(['name' => 'रमेश कुमार']);

        $this->actingAs($user)->get('/language/hi');
        $this->actingAs($user)->get('/account')->assertOk()->assertSee('>र<', false)->assertDontSee("ï¿½", false);
    }

    public function test_the_page_not_found_screen_speaks_the_chosen_language(): void
    {
        $this->get('/language/hi');

        $this->get('/no-such-page')->assertNotFound()->assertSee('lang="hi"', false)->assertSee($this->hi('Page not found'));
    }

    public function test_the_main_pages_open_in_hindi(): void
    {
        $this->get('/language/hi');

        foreach (['/home', '/breeding', '/breeding/match', '/vets', '/caretakers', '/about', '/contact', '/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('lang="hi"', false)->assertSee('noto-sans-devanagari', false);
        }
    }

    // ---- The Hindi file must keep up with the screens --------------------------------------------------------

    /** @return array<int, string> every English string the views and code ask to translate */
    private function translatableStrings(): array
    {
        $found = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('resources/views'), \FilesystemIterator::SKIP_DOTS));
        $files = iterator_to_array($iterator);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('app'), \FilesystemIterator::SKIP_DOTS));
        $files += iterator_to_array($iterator);

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.php')) {
                continue;
            }
            $source = file_get_contents($file->getPathname());

            if (preg_match_all("/(?:__|trans|trans_choice|@lang)\(\s*'((?:[^'\\\\]|\\\\.)*)'/s", $source, $m)) {
                foreach ($m[1] as $raw) {
                    $found[] = str_replace(["\\'", '\\\\'], ["'", '\\'], $raw);
                }
            }
            if (preg_match_all('/(?:__|trans|trans_choice|@lang)\(\s*"((?:[^"\\\\]|\\\\.)*)"/s', $source, $m)) {
                foreach ($m[1] as $raw) {
                    $found[] = stripcslashes($raw);
                }
            }
        }

        // "auth.failed" style keys live in lang/hi/*.php, not in the JSON file.
        return array_values(array_unique(array_filter($found, fn ($s) => ! preg_match('/^[a-z_]+\.[a-z_.]+$/', $s))));
    }

    public function test_every_string_on_the_screens_has_a_hindi_translation(): void
    {
        $hindi = $this->hindi();
        $missing = array_values(array_filter($this->translatableStrings(), fn ($s) => ! array_key_exists($s, $hindi)));

        $this->assertSame([], $missing, "These strings have no Hindi translation in lang/hi.json:\n".implode("\n", $missing));
    }

    public function test_hindi_translations_keep_their_placeholders_and_are_really_hindi(): void
    {
        $problems = [];
        foreach ($this->hindi() as $english => $hindi) {
            preg_match_all('/:[a-zA-Z_]+/', $english, $a);
            preg_match_all('/:[a-zA-Z_]+/', $hindi, $b);
            sort($a[0]);
            sort($b[0]);
            if ($a[0] !== $b[0]) {
                $problems[] = "placeholders differ: {$english}";
            }
            if (trim($hindi) === '') {
                $problems[] = "empty: {$english}";
            }
            $brand = in_array($english, ['WhatsApp'], true); // names that stay in Latin letters
            if (! $brand && preg_match('/[A-Za-z]{4,}/', preg_replace('/:[a-zA-Z_]+/', '', $english)) && ! preg_match('/\p{Devanagari}/u', $hindi)) {
                $problems[] = "not Hindi: {$english} => {$hindi}";
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }
}
