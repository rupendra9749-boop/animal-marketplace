<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Caretaker;
use App\Models\Category;
use App\Models\User;
use App\Models\Vet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPanelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function registration(): array
    {
        return [
            'name' => 'New Person', 'email' => 'new@example.com', 'phone' => '98765 43210',
            'state' => 'Punjab', 'city' => 'Ludhiana', 'password' => 'password', 'password_confirmation' => 'password',
        ];
    }

    public function test_a_seller_can_do_everything_a_provider_can_and_a_buyer_cannot(): void
    {
        $seller = User::factory()->seller()->create();
        $buyer = User::factory()->create();

        foreach (['/seller/dashboard', '/breeder/animals/create', '/doctor/profile', '/caretaker/profile'] as $path) {
            $this->actingAs($seller)->get($path)->assertOk();
            $this->actingAs($buyer)->get($path)->assertForbidden();
        }

        // ...and still shops like anyone.
        $this->actingAs($seller)->get('/account')->assertOk();
        $this->actingAs($seller)->get('/cart')->assertOk();
    }

    public function test_an_admin_can_open_every_panel(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['/admin/dashboard', '/seller/dashboard', '/breeder/animals', '/doctor/profile', '/caretaker/profile', '/account'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_a_buyer_can_become_a_seller_with_one_tap(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get('/account')->assertSee('Become a seller');
        $this->actingAs($buyer)->post(route('account.become-seller'))->assertRedirect(route('seller.dashboard'));

        $this->assertTrue($buyer->fresh()->is_seller);
        $this->actingAs($buyer->fresh())->get('/doctor/profile')->assertOk();
    }

    public function test_an_unknown_account_type_is_rejected_so_nobody_can_register_as_admin(): void
    {
        $this->post('/register', $this->registration() + ['account_type' => 'admin'])->assertSessionHasErrors('account_type');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_dashboard_link_sends_each_person_to_their_own_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertRedirect(route('account.dashboard'));
        $this->actingAs(User::factory()->seller()->create())->get('/dashboard')->assertRedirect(route('seller.dashboard'));
        $this->actingAs(User::factory()->breeder()->create())->get('/dashboard')->assertRedirect(route('breeder.dashboard'));
        $this->actingAs(User::factory()->doctor()->create())->get('/dashboard')->assertRedirect(route('doctor.dashboard'));
        $this->actingAs(User::factory()->caretaker()->create())->get('/dashboard')->assertRedirect(route('caretaker.dashboard'));
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_each_panel_is_only_open_to_people_with_that_role(): void
    {
        $this->get('/doctor/dashboard')->assertRedirect(route('login'));

        $buyer = User::factory()->create();
        $doctor = User::factory()->doctor()->create();

        foreach (['/doctor/dashboard', '/caretaker/dashboard', '/breeder/dashboard', '/seller/dashboard', '/admin/dashboard'] as $path) {
            $this->actingAs($buyer)->get($path)->assertForbidden();
        }

        $this->actingAs($doctor)->get('/doctor/dashboard')->assertOk();
        $this->actingAs($doctor)->get('/caretaker/dashboard')->assertForbidden();
        $this->actingAs($doctor)->get('/breeder/animals')->assertForbidden();
    }

    public function test_a_suspended_person_cannot_use_their_panel(): void
    {
        $doctor = User::factory()->doctor()->create(['is_active' => false]);

        $this->actingAs($doctor)->get('/doctor/dashboard')->assertForbidden();
    }

    public function test_a_doctor_creates_a_profile_that_is_live_at_once_and_can_be_edited_freely(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);
        $doctor = User::factory()->doctor()->create(['name' => 'Dr. Panel']);

        $this->actingAs($doctor)->get('/doctor/dashboard')->assertOk()->assertSee('You have not created your profile yet');
        $this->actingAs($doctor)->get('/doctor/profile')->assertOk()->assertSee('Dr. Panel');

        $this->actingAs($doctor)->put('/doctor/profile', [
            'name' => 'Dr. Panel', 'phone' => '98765 43210', 'state' => 'Delhi', 'city' => 'Delhi', 'categories' => [$dog->id], 'clinic_name' => 'Panel Clinic',
        ])->assertRedirect(route('doctor.dashboard'));

        $vet = $doctor->fresh()->vet;
        $this->assertNotNull($vet);
        $this->assertTrue($vet->is_active);

        $this->actingAs($doctor->fresh())->get('/doctor/dashboard')->assertSee('Your profile is live');
        $this->fromCity('Delhi')->get('/vets')->assertSee('Dr. Panel');

        // An admin can still hide it, and the panel says so.
        $vet->update(['is_active' => false]);
        $this->actingAs($doctor->fresh())->get('/doctor/dashboard')->assertSee('Your profile is hidden');
        $vet->update(['is_active' => true]);
        $this->actingAs($doctor->fresh())->put('/doctor/profile', [
            'name' => 'Dr. Panel', 'phone' => '98765 43210', 'state' => 'Delhi', 'city' => 'Delhi', 'categories' => [$dog->id], 'clinic_name' => 'Renamed Clinic',
        ])->assertRedirect(route('doctor.dashboard'));

        $vet->refresh();
        $this->assertSame('Renamed Clinic', $vet->clinic_name);
        $this->assertTrue($vet->is_active, 'editing must not hide an approved profile');
        $this->assertSame(1, Vet::where('user_id', $doctor->id)->count());
        $this->actingAs($doctor->fresh())->get('/doctor/dashboard')->assertSee('Your profile is live');
    }

    public function test_a_caretaker_creates_a_profile_that_is_live_at_once(): void
    {
        $dog = Category::factory()->create(['name' => 'Dog']);
        $caretaker = User::factory()->caretaker()->create();

        $this->actingAs($caretaker)->get('/caretaker/dashboard')->assertOk()->assertSee('You have not created your profile yet');

        $this->actingAs($caretaker)->put('/caretaker/profile', [
            'name' => 'Care Person', 'phone' => '98765 43210', 'state' => 'Delhi', 'city' => 'Delhi', 'categories' => [$dog->id], 'rate' => 300, 'rate_unit' => 'day', 'available' => '1',
        ])->assertRedirect(route('caretaker.dashboard'));

        $profile = $caretaker->fresh()->caretaker;
        $this->assertNotNull($profile);
        $this->assertTrue($profile->is_active);
        $this->assertTrue($profile->available);
        $this->actingAs($caretaker->fresh())->get('/caretaker/dashboard')->assertSee('Your profile is live');
        $this->fromCity('Delhi')->get('/caretakers')->assertSee('Care Person');
        $this->assertSame(1, Caretaker::where('user_id', $caretaker->id)->count());
    }

    public function test_a_breeder_can_only_list_breeding_animals_and_only_manage_their_own(): void
    {
        $breeder = User::factory()->breeder()->create();
        $other = User::factory()->breeder()->create();
        $category = Category::factory()->create();

        // The form tries to sneak in a normal sale listing; a breeder's listing is always breeding.
        $this->actingAs($breeder)->post('/breeder/animals', [
            'name' => 'Breeder Stud', 'category_id' => $category->id, 'gender' => 'male',
            'listing_type' => 'sale', 'price' => 9999, 'stock' => 5, 'breeding_fee' => 300,
            'state' => 'Punjab', 'city' => 'Ludhiana', 'is_active' => '1',
        ])->assertRedirect(route('breeder.animals.index'));

        $animal = Animal::where('name', 'Breeder Stud')->firstOrFail();
        $this->assertSame('breeding', $animal->listing_type);
        $this->assertEquals(0, $animal->price);
        $this->assertEquals(300, $animal->breeding_fee);
        $this->assertSame($breeder->id, $animal->user_id);

        // Their sale animals (if they are also a seller) do not clutter the breeder list.
        $sale = Animal::factory()->create(['user_id' => $breeder->id, 'listing_type' => 'sale']);

        $this->actingAs($breeder)->get('/breeder/animals')->assertOk()->assertSee('Breeder Stud')->assertDontSee($sale->name);
        $this->actingAs($breeder)->get('/breeder/dashboard')->assertOk()->assertSee('Breeder Dashboard');

        $this->actingAs($other)->get(route('breeder.animals.edit', $animal))->assertForbidden();
        $this->actingAs($other)->delete(route('breeder.animals.destroy', $animal))->assertForbidden();
        $this->actingAs($breeder)->delete(route('breeder.animals.destroy', $animal))->assertRedirect(route('breeder.animals.index'));
    }

    public function test_admin_makes_people_sellers_but_cannot_lock_themselves_out(): void
    {
        $admin = User::factory()->admin()->create();
        $person = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.update', $person), ['is_seller' => '1', 'is_active' => '1'])->assertRedirect();
        $person->refresh();
        $this->assertTrue($person->isSeller() && $person->isDoctor() && $person->isCaretaker() && $person->isBreeder());
        $this->assertFalse($person->isAdmin());

        $this->actingAs($admin)->patch(route('admin.users.update', $person), ['is_active' => '1']);
        $this->assertFalse($person->fresh()->isSeller());

        // Trying to remove their own admin role and suspend themselves does nothing.
        $this->actingAs($admin)->patch(route('admin.users.update', $admin), ['is_seller' => '1']);
        $admin->refresh();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->is_active);

        $this->actingAs($person)->patch(route('admin.users.update', $admin), ['is_admin' => '0'])->assertForbidden();
    }

    public function test_the_users_page_can_filter_by_role_and_search_by_phone_or_city(): void
    {
        $admin = User::factory()->admin()->create();
        $doctor = User::factory()->doctor()->create(['name' => 'Only Doctor']);
        $buyer = User::factory()->create(['name' => 'Only Buyer', 'phone' => '+91 91111 22222']);

        $this->actingAs($admin)->get('/admin/users?role=doctor')->assertSee('Only Doctor')->assertDontSee('Only Buyer');
        $this->actingAs($admin)->get('/admin/users?role=buyer')->assertSee('Only Buyer')->assertDontSee('Only Doctor');
        $this->actingAs($admin)->get('/admin/users?search=91111')->assertSee('Only Buyer')->assertDontSee('Only Doctor');
    }

    public function test_admin_breeding_overview_lists_breeding_animals_with_stats(): void
    {
        $admin = User::factory()->admin()->create();
        $stud = Animal::factory()->create(['listing_type' => 'breeding', 'gender' => 'male', 'breeding_fee' => 200]);
        $sale = Animal::factory()->create(['listing_type' => 'sale']);

        $this->actingAs(User::factory()->seller()->create())->get('/admin/breeding')->assertForbidden();

        $this->actingAs($admin)->get('/admin/breeding')->assertOk()->assertSee($stud->name)->assertDontSee($sale->name)->assertSee('₹200');
    }

    public function test_a_buyer_can_message_a_doctor_or_caretaker_but_not_an_ordinary_buyer(): void
    {
        $buyer = User::factory()->create();
        $doctor = User::factory()->doctor()->create();
        $plain = User::factory()->create();

        $this->actingAs($buyer)->post(route('messages.provider', $doctor))->assertRedirect();
        $this->assertDatabaseCount('conversations', 1);

        $this->actingAs($buyer)->post(route('messages.provider', $plain))->assertForbidden();
        $this->actingAs($buyer)->post(route('messages.provider', $buyer))->assertStatus(400);

        $this->actingAs($doctor)->get('/messages')->assertOk();
    }

    public function test_a_sellers_sidebar_holds_selling_breeding_doctor_and_caretaker_in_one_place(): void
    {
        $seller = User::factory()->seller()->create();

        $page = $this->actingAs($seller)->get('/doctor/dashboard')->assertOk();
        $page->assertSee(route('seller.animals.index'), false)
            ->assertSee(route('breeder.animals.index'), false)
            ->assertSee(route('doctor.profile'), false)
            ->assertSee(route('caretaker.profile'), false)
            ->assertSee('Seller Panel')
            ->assertSee(route('account.dashboard'), false);
    }

    public function test_a_person_with_only_the_doctor_flag_keeps_their_own_small_panel(): void
    {
        $doctor = User::factory()->doctor()->create();

        $page = $this->actingAs($doctor)->get('/doctor/dashboard')->assertOk();
        $page->assertSee('Doctor Panel')->assertDontSee(route('seller.animals.index'), false);
    }

    public function test_people_without_contact_details_are_reminded_to_add_them(): void
    {
        $person = User::factory()->create(['phone' => null, 'city' => null, 'state' => null, 'latitude' => null, 'longitude' => null]);

        $this->actingAs($person)->get('/account')->assertSee('Add your mobile number and city');
        $this->actingAs(User::factory()->create())->get('/account')->assertDontSee('Add your mobile number and city');
    }
}
