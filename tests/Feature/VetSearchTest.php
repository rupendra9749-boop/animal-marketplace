<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VetSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function vet(string $city, array $attributes = []): Vet
    {
        return Vet::factory()->inCity($city)->create($attributes);
    }

    public function test_only_active_doctors_are_listed(): void
    {
        $live = $this->vet('Delhi');
        $waiting = $this->vet('Delhi', ['is_active' => false]);

        $this->fromCity('Delhi')->get('/vets')->assertOk()->assertSee($live->name)->assertDontSee($waiting->name);
    }

    public function test_doctors_are_only_shown_within_50_km_nearest_first(): void
    {
        $delhi = $this->vet('Delhi');
        $gurugram = $this->vet('Gurugram');   // ~ 25 km from Delhi
        $jaipur = $this->vet('Jaipur');       // ~ 235 km
        $mumbai = $this->vet('Mumbai');

        $response = $this->fromCity('Delhi')->get('/vets')->assertOk();

        $response->assertSeeInOrder([$delhi->name, $gurugram->name]);
        $response->assertDontSee($jaipur->name)->assertDontSee($mumbai->name);
    }

    public function test_the_distance_can_be_narrowed_but_never_widened_past_50_km(): void
    {
        $delhi = $this->vet('Delhi');
        $gurugram = $this->vet('Gurugram');
        $jaipur = $this->vet('Jaipur');

        $narrow = $this->fromCity('Delhi')->get('/vets?radius=10')->assertOk();
        $narrow->assertSee($delhi->name)->assertDontSee($gurugram->name);

        // 250 is not an allowed choice any more: it falls back to 50 km.
        $wide = $this->fromCity('Delhi')->get('/vets?radius=250')->assertOk();
        $wide->assertSee($gurugram->name)->assertDontSee($jaipur->name);
    }

    public function test_a_doctors_by_city_link_searches_around_that_city(): void
    {
        $jaipur = $this->vet('Jaipur');

        $this->fromCity('Delhi')->get('/vets?state=Rajasthan&city=Jaipur')->assertSee($jaipur->name);
    }

    public function test_without_a_location_the_doctor_page_asks_for_one(): void
    {
        $vet = $this->vet('Delhi');

        $this->get('/vets')->assertOk()->assertSee('Where are you?')->assertDontSee($vet->name);
    }

    public function test_search_can_filter_by_animal_treated_and_emergency_care(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);
        $cow = Category::factory()->create(['name' => 'Cow']);

        $dogVet = $this->vet('Delhi');
        $dogVet->categories()->attach($dog);
        $cowVet = $this->vet('Delhi', ['emergency' => true]);
        $cowVet->categories()->attach($cow);

        $this->fromCity('Delhi')->get('/vets?type='.$dog->id)->assertSee($dogVet->name)->assertDontSee($cowVet->name);
        $this->fromCity('Delhi')->get('/vets?emergency=1')->assertSee($cowVet->name)->assertDontSee($dogVet->name);
    }

    public function test_doctor_page_shows_call_link_and_hides_unapproved_doctors(): void
    {
        $vet = $this->vet('Delhi', ['phone' => '+91 98765 43210', 'whatsapp' => '+91 98765 43210']);
        $waiting = $this->vet('Delhi', ['is_active' => false]);

        $this->get(route('vets.show', $vet))
            ->assertOk()
            ->assertSee('tel:+919876543210', false)
            ->assertSee('wa.me/919876543210', false);

        $this->get(route('vets.show', $waiting))->assertNotFound();
    }

    public function test_listing_a_clinic_requires_login_and_goes_live_at_once(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);

        $this->get('/vets/register')->assertRedirect(route('login'));

        // A plain buyer is told that sellers list clinics, and cannot post one.
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->get('/vets/register')->assertOk()->assertSee('Become a seller');
        $this->actingAs($buyer)->post(route('vets.store'), ['name' => 'X'])->assertForbidden();

        $user = User::factory()->seller()->create();
        $this->actingAs($user)->get('/vets/register')->assertOk();

        $this->actingAs($user)->post(route('vets.store'), [
            'name' => 'Dr. Test Sharma',
            'phone' => '98765 43210',
            'state' => 'Delhi',
            'city' => 'Delhi',
            'categories' => [$dog->id],
            'home_visit' => '1',
        ])->assertRedirect(route('doctor.dashboard'));

        $vet = Vet::where('name', 'Dr. Test Sharma')->firstOrFail();
        $this->assertTrue($vet->is_active, 'a clinic listed by a seller is visible as soon as it is saved');
        $this->assertTrue($vet->home_visit);
        $this->assertSame($user->id, $vet->user_id);
        $this->assertSame('+91 98765 43210', $vet->phone);
        $this->assertSame('Delhi', $vet->state);
        $this->assertEqualsWithDelta(28.7, $vet->latitude, 0.2);
        $this->assertSame([$dog->id], $vet->categories->pluck('id')->all());

        $this->fromCity('Delhi')->get('/vets')->assertSee('Dr. Test Sharma');

        // A second profile is not created; the person is sent to edit the one they have.
        $this->actingAs($user->fresh())->get('/vets/register')->assertRedirect(route('doctor.profile'));
    }

    public function test_clinic_form_rejects_bad_phone_unknown_city_and_missing_animal_type(): void
    {
        $user = User::factory()->seller()->create();

        $this->actingAs($user)->post(route('vets.store'), ['name' => 'Dr. X', 'phone' => 'call me', 'state' => 'Punjab', 'city' => 'Atlantis'])
            ->assertSessionHasErrors(['phone', 'city', 'categories']);

        $this->assertDatabaseCount('vets', 0);
    }

    public function test_clinics_may_use_a_landline_with_an_std_code(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);
        $user = User::factory()->seller()->create();

        $this->actingAs($user)->post(route('vets.store'), [
            'name' => 'Dr. Landline', 'phone' => '0161 2345678', 'state' => 'Punjab', 'city' => 'Ludhiana', 'categories' => [$dog->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('01612345678', Vet::where('name', 'Dr. Landline')->firstOrFail()->phone);
    }

    public function test_only_admins_can_manage_doctors_and_approve_listings(): void
    {
        $waiting = $this->vet('Delhi', ['is_active' => false]);

        $this->actingAs(User::factory()->create())->get('/admin/vets')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/vets')->assertOk()->assertSee($waiting->name);

        $this->actingAs($admin)->patch(route('admin.vets.toggle', $waiting))->assertRedirect();
        $this->assertTrue($waiting->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.vets.destroy', $waiting))->assertRedirect(route('admin.vets.index'));
        $this->assertDatabaseMissing('vets', ['id' => $waiting->id]);
    }

    public function test_admin_can_add_a_doctor_who_is_visible_straight_away(): void
    {
        $cow = Category::factory()->create(['name' => 'Cow']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.vets.store'), [
            'name' => 'Dr. Admin Added',
            'phone' => '98765 00000',
            'state' => 'Haryana',
            'city' => 'Karnal',
            'categories' => [$cow->id],
            'emergency' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.vets.index'));

        $this->fromCity('Karnal')->get('/vets')->assertSee('Dr. Admin Added');
    }
}
