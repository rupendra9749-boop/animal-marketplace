<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function details(array $overrides = []): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '98765 43210',
            'state' => 'Punjab',
            'city' => 'Ludhiana',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee('Mobile number')->assertSee('State / Union Territory')->assertSee('India');
    }

    public function test_new_users_can_register_with_phone_and_location(): void
    {
        $response = $this->post('/register', $this->details());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('+91 98765 43210', $user->phone);
        $this->assertSame('Punjab', $user->state);
        $this->assertSame('Ludhiana', $user->city);
        $this->assertSame('India', $user->country);
        $this->assertEqualsWithDelta(30.9, $user->latitude, 0.1);
        $this->assertEqualsWithDelta(75.85, $user->longitude, 0.1);
        $this->assertFalse($user->isSeller());
    }

    public function test_a_phone_can_be_typed_in_any_common_format(): void
    {
        foreach (['+91 98765 43210', '919876543210', '09876543210', '98765-43210'] as $i => $typed) {
            $this->post('/register', $this->details(['email' => "user{$i}@example.com", 'phone' => $typed]));
            $this->assertSame('+91 98765 43210', User::where('email', "user{$i}@example.com")->first()?->phone ?? null, "typed as {$typed}");
            auth()->logout();
            User::query()->delete();
        }
    }

    public function test_bad_phone_numbers_and_unknown_cities_are_rejected(): void
    {
        $this->post('/register', $this->details(['phone' => '12345']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->details(['phone' => '5987654321']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->details(['city' => 'Atlantis']))->assertSessionHasErrors('city');
        $this->post('/register', $this->details(['city' => 'Jaipur']))->assertSessionHasErrors('city'); // Jaipur is not in Punjab
        $this->post('/register', $this->details(['state' => '', 'city' => '']))->assertSessionHasErrors(['state', 'city']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_phone_number_can_only_be_used_once(): void
    {
        User::factory()->create(['phone' => '+91 98765 43210']);

        $this->post('/register', $this->details())->assertSessionHasErrors('phone');
    }

    public function test_people_register_as_a_buyer_by_default_or_as_a_seller(): void
    {
        $this->post('/register', $this->details());
        $this->assertFalse(User::where('email', 'test@example.com')->firstOrFail()->isSeller());
        auth()->logout();

        $this->post('/register', $this->details(['email' => 'seller@example.com', 'phone' => '98111 22233', 'account_type' => 'seller']));
        $seller = User::where('email', 'seller@example.com')->firstOrFail();
        $this->assertTrue($seller->isSeller());
        $this->assertTrue($seller->isDoctor() && $seller->isCaretaker() && $seller->isBreeder(), 'a seller can also breed, be a doctor and a caretaker');
    }

    public function test_the_register_page_offers_buyer_or_seller(): void
    {
        $this->get('/register')->assertOk()->assertSee('Buy animals and use services')->assertSee('Sell and offer services');
    }
}
