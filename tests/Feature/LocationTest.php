<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_the_city_list_covers_all_states_and_returns_cities_of_a_state(): void
    {
        $cities = $this->getJson('/locations/cities?state=Punjab')->assertOk()->json();

        $this->assertContains('Ludhiana', $cities);
        $this->assertContains('Amritsar', $cities);
        $this->assertNotContains('Jaipur', $cities);
        $this->assertGreaterThan(100, count($cities));
        $this->assertSame([], $this->getJson('/locations/cities?state=Atlantis')->json());
    }

    public function test_the_register_page_lists_every_state_and_union_territory(): void
    {
        $page = $this->get('/register')->assertOk();

        foreach (['Andaman and Nicobar Islands', 'Kerala', 'Ladakh', 'Uttar Pradesh', 'West Bengal', 'Delhi', 'Lakshadweep'] as $state) {
            $page->assertSee($state);
        }
    }

    public function test_a_visitor_can_pick_a_city_and_it_is_remembered(): void
    {
        $this->from('/home')->post('/location', ['state' => 'Rajasthan', 'city' => 'Jaipur'])->assertRedirect('http://localhost/home');

        $this->assertSame('Jaipur', session('location.city'));
        $this->assertSame('Rajasthan', session('location.state'));
        $this->assertEqualsWithDelta(26.9, session('location.lat'), 0.3);
    }

    public function test_picking_a_city_removes_the_temporary_city_link_parameters_from_the_page(): void
    {
        $this->from('/vets?state=Punjab&city=Ludhiana&radius=25&page=2')->post('/location', ['state' => 'Delhi', 'city' => 'Delhi'])
            ->assertRedirect('http://localhost/vets?radius=25');
    }

    public function test_the_redirect_keeps_the_app_sub_folder_instead_of_doubling_it(): void
    {
        // Live, the app is served from /marketplace and the browser's referrer already contains that folder.
        $this->withServerVariables(['HTTP_REFERER' => 'http://localhost/marketplace/vets?state=Punjab&city=Ludhiana&page=2'])
            ->post('/location', ['state' => 'Delhi', 'city' => 'Delhi'])
            ->assertRedirect('http://localhost/marketplace/vets');
    }

    public function test_an_unknown_city_is_not_accepted(): void
    {
        $this->from('/home')->post('/location', ['state' => 'Punjab', 'city' => 'Atlantis'])->assertRedirect('http://localhost/home');
        $this->assertNull(session('location'));

        $this->from('/home')->post('/location', ['state' => 'Punjab'])->assertSessionHasErrors('city');
    }

    public function test_gps_coordinates_are_turned_into_the_nearest_city(): void
    {
        $this->postJson('/location/detect', ['lat' => 30.91, 'lng' => 75.85])
            ->assertOk()
            ->assertJson(['city' => 'Ludhiana', 'state' => 'Punjab']);

        $this->assertSame('Ludhiana', session('location.city'));
    }

    public function test_coordinates_outside_india_are_refused(): void
    {
        $this->postJson('/location/detect', ['lat' => 51.5, 'lng' => -0.12])->assertStatus(422);

        $this->assertNull(session('location'));
    }

    public function test_the_location_can_be_cleared(): void
    {
        $this->fromCity('Delhi')->from('/home')->delete('/location')->assertRedirect('http://localhost/home');

        $this->assertNull(session('location'));
    }

    public function test_registering_or_editing_the_profile_city_replaces_a_city_picked_earlier(): void
    {
        // A guest browsed as Delhi, then registers in Ludhiana.
        $this->fromCity('Delhi')->post('/register', [
            'name' => 'Switcher', 'email' => 'switch@example.com', 'phone' => '98765 43210', 'state' => 'Punjab', 'city' => 'Ludhiana',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);
        $this->assertNull(session('location'));
        $this->get('/home')->assertSee('Animals near Ludhiana');

        // They pick Jaipur for a moment, then change their profile city to Amritsar: the profile wins.
        $this->post('/location', ['state' => 'Rajasthan', 'city' => 'Jaipur']);
        $this->patch('/profile', ['name' => 'Switcher', 'email' => 'switch@example.com', 'phone' => '98765 43210', 'state' => 'Punjab', 'city' => 'Amritsar']);
        $this->assertNull(session('location'));
        $this->get('/home')->assertSee('Animals near Amritsar');
    }
}
