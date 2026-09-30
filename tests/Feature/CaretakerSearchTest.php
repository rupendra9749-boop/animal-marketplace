<?php

namespace Tests\Feature;

use App\Models\Caretaker;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaretakerSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function caretaker(string $city, array $attributes = []): Caretaker
    {
        return Caretaker::factory()->inCity($city)->create($attributes);
    }

    public function test_only_active_caretakers_are_listed(): void
    {
        $live = $this->caretaker('Delhi');
        $waiting = $this->caretaker('Delhi', ['is_active' => false]);

        $this->fromCity('Delhi')->get('/caretakers')->assertOk()->assertSee($live->name)->assertDontSee($waiting->name);
    }

    public function test_caretakers_are_only_shown_within_50_km_nearest_first(): void
    {
        $delhi = $this->caretaker('Delhi');
        $gurugram = $this->caretaker('Gurugram');
        $jaipur = $this->caretaker('Jaipur');

        $response = $this->fromCity('Delhi')->get('/caretakers')->assertOk();

        $response->assertSeeInOrder([$delhi->name, $gurugram->name]);
        $response->assertDontSee($jaipur->name);
    }

    public function test_the_distance_can_be_narrowed_but_not_widened_past_50_km(): void
    {
        $delhi = $this->caretaker('Delhi');
        $gurugram = $this->caretaker('Gurugram');
        $jaipur = $this->caretaker('Jaipur');

        $this->fromCity('Delhi')->get('/caretakers?radius=10')->assertSee($delhi->name)->assertDontSee($gurugram->name);
        $this->fromCity('Delhi')->get('/caretakers?radius=500')->assertSee($gurugram->name)->assertDontSee($jaipur->name);
    }

    public function test_without_a_location_the_page_asks_for_one(): void
    {
        $caretaker = $this->caretaker('Delhi');

        $this->get('/caretakers')->assertOk()->assertSee('Where are you?')->assertDontSee($caretaker->name);
    }

    public function test_search_filters_by_animal_boarding_and_availability(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);
        $cow = Category::factory()->create(['name' => 'Cow']);

        $dogSitter = $this->caretaker('Delhi', ['boarding' => true]);
        $dogSitter->categories()->attach($dog);
        $farmHand = $this->caretaker('Delhi', ['available' => false]);
        $farmHand->categories()->attach($cow);

        $this->fromCity('Delhi')->get('/caretakers?type='.$dog->id)->assertSee($dogSitter->name)->assertDontSee($farmHand->name);
        $this->fromCity('Delhi')->get('/caretakers?boarding=1')->assertSee($dogSitter->name)->assertDontSee($farmHand->name);
        $this->fromCity('Delhi')->get('/caretakers?available=1')->assertSee($dogSitter->name)->assertDontSee($farmHand->name);
    }

    public function test_profile_page_shows_rate_and_contact_and_hides_unapproved_profiles(): void
    {
        $caretaker = $this->caretaker('Delhi', ['phone' => '+91 98765 43210', 'whatsapp' => '+91 98765 43210', 'rate' => 750, 'rate_unit' => 'day']);
        $waiting = $this->caretaker('Delhi', ['is_active' => false]);

        $this->get(route('caretakers.show', $caretaker))
            ->assertOk()
            ->assertSee('₹750 per day')
            ->assertSee('tel:+919876543210', false)
            ->assertSee('wa.me/919876543210', false);

        $this->get(route('caretakers.show', $waiting))->assertNotFound();
    }

    public function test_offering_services_requires_login_goes_live_at_once_and_gives_the_caretaker_panel(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);

        $this->get('/caretakers/register')->assertRedirect(route('login'));

        // A plain buyer is told that sellers offer services, and cannot post one.
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->get('/caretakers/register')->assertOk()->assertSee('Become a seller');
        $this->actingAs($buyer)->post(route('caretakers.store'), ['name' => 'X'])->assertForbidden();

        $user = User::factory()->seller()->create();
        $this->actingAs($user)->get('/caretakers/register')->assertOk();

        $this->actingAs($user)->post(route('caretakers.store'), [
            'name' => 'Test Caretaker',
            'phone' => '98765 43210',
            'state' => 'Punjab',
            'city' => 'Ludhiana',
            'categories' => [$dog->id],
            'rate' => 400,
            'rate_unit' => 'day',
            'boarding' => '1',
        ])->assertRedirect(route('caretaker.dashboard'));

        $caretaker = Caretaker::where('name', 'Test Caretaker')->firstOrFail();
        $this->assertTrue($caretaker->is_active, 'a caretaker profile made by a seller is visible as soon as it is saved');
        $this->assertTrue($caretaker->boarding);
        $this->assertSame($user->id, $caretaker->user_id);
        $this->assertSame('Punjab', $caretaker->state);

        $this->fromCity('Ludhiana')->get('/caretakers')->assertSee('Test Caretaker');

        // A second profile is not created; the person is sent to edit the one they have.
        $this->actingAs($user->fresh())->get('/caretakers/register')->assertRedirect(route('caretaker.profile'));
    }

    public function test_the_form_rejects_a_bad_phone_an_unknown_city_and_no_animals(): void
    {
        $user = User::factory()->seller()->create();

        $this->actingAs($user)->post(route('caretakers.store'), ['name' => 'X', 'phone' => 'abc', 'state' => 'Punjab', 'city' => 'Atlantis'])
            ->assertSessionHasErrors(['phone', 'city', 'categories']);

        $this->assertDatabaseCount('caretakers', 0);
    }

    public function test_admin_manages_and_approves_caretakers(): void
    {
        $waiting = $this->caretaker('Delhi', ['is_active' => false]);

        $this->actingAs(User::factory()->create())->get('/admin/caretakers')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/caretakers')->assertOk()->assertSee($waiting->name);

        $this->actingAs($admin)->patch(route('admin.caretakers.toggle', $waiting))->assertRedirect();
        $this->assertTrue($waiting->fresh()->is_active);

        $cow = Category::factory()->create(['name' => 'Cow']);
        $this->actingAs($admin)->post(route('admin.caretakers.store'), [
            'name' => 'Admin Added', 'phone' => '98765 00000', 'state' => 'Haryana', 'city' => 'Karnal', 'categories' => [$cow->id], 'is_active' => '1',
        ])->assertRedirect(route('admin.caretakers.index'));
        $this->fromCity('Karnal')->get('/caretakers')->assertSee('Admin Added');

        $this->actingAs($admin)->delete(route('admin.caretakers.destroy', $waiting))->assertRedirect(route('admin.caretakers.index'));
        $this->assertDatabaseMissing('caretakers', ['id' => $waiting->id]);
    }
}
