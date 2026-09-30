<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use App\Support\Locations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreedingListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function animal(string $type, string $city = 'Delhi', array $attributes = []): Animal
    {
        $place = Locations::findByCity($city);

        return Animal::factory()->create([
            'listing_type' => $type,
            'location' => $place['city'],
            'state' => $place['state'],
            'latitude' => $place['lat'],
            'longitude' => $place['lng'],
            'stock' => 3,
            ...$attributes,
        ]);
    }

    public function test_shop_shows_sale_and_both_but_hides_breeding_only_animals(): void
    {
        $sale = $this->animal('sale');
        $both = $this->animal('both');
        $breedingOnly = $this->animal('breeding');

        $this->fromCity('Delhi')->get('/home')
            ->assertOk()
            ->assertSee($sale->name)
            ->assertSee($both->name)
            ->assertDontSee($breedingOnly->name);
    }

    public function test_breeding_page_shows_breeding_and_both_but_not_sale_only_animals(): void
    {
        $sale = $this->animal('sale');
        $both = $this->animal('both', attributes: ['breeding_fee' => 75]);
        $breedingOnly = $this->animal('breeding', attributes: ['breeding_fee' => 120]);

        $this->fromCity('Delhi')->get('/breeding')
            ->assertOk()
            ->assertSee($both->name)
            ->assertSee($breedingOnly->name)
            ->assertSee('₹120')
            ->assertDontSee($sale->name);
    }

    public function test_breeding_page_only_shows_animals_within_250_km_nearest_first_and_filters_by_sex(): void
    {
        $delhiMale = $this->animal('breeding', 'Delhi', ['gender' => 'male']);
        $gurugramMale = $this->animal('breeding', 'Gurugram', ['gender' => 'male']);
        $jaipurMale = $this->animal('breeding', 'Jaipur', ['gender' => 'male']);   // ~ 235 km
        $delhiFemale = $this->animal('breeding', 'Delhi', ['gender' => 'female']);
        $mumbaiMale = $this->animal('breeding', 'Mumbai', ['gender' => 'male']);  // ~ 1,150 km

        $response = $this->fromCity('Delhi')->get('/breeding?sex=male')->assertOk();

        $response->assertSeeInOrder([$delhiMale->name, $gurugramMale->name, $jaipurMale->name]);
        $response->assertDontSee($mumbaiMale->name);
        $response->assertDontSee($delhiFemale->name);
    }

    public function test_animals_beyond_250_km_are_not_shown_in_the_shop_either(): void
    {
        $near = $this->animal('sale', 'Jaipur');
        $far = $this->animal('sale', 'Mumbai');

        $this->fromCity('Delhi')->get('/home')->assertSee($near->name)->assertDontSee($far->name);
        $this->fromCity('Mumbai')->get('/home')->assertSee($far->name)->assertDontSee($near->name);
    }

    public function test_without_a_location_the_shop_and_breeding_pages_ask_for_one_instead_of_listing_animals(): void
    {
        $sale = $this->animal('sale');
        $breeding = $this->animal('breeding');

        $this->get('/home')->assertOk()->assertSee('Where are you?')->assertDontSee($sale->name);
        $this->get('/breeding')->assertOk()->assertSee('Where are you?')->assertDontSee($breeding->name);
    }

    public function test_a_logged_in_persons_profile_city_is_used_as_their_location(): void
    {
        $sale = $this->animal('sale', 'Ludhiana');
        $user = User::factory()->inCity('Ludhiana')->create();

        $this->actingAs($user)->get('/home')->assertSee($sale->name)->assertDontSee('Where are you?');
    }

    public function test_old_match_checker_links_still_reach_the_checker(): void
    {
        $this->get('/breeding?a=1&b=2')->assertRedirect(route('breeding.index', ['a' => 1, 'b' => 2]));
        $this->get('/breeding/match')->assertOk();
    }

    public function test_breeding_only_animals_cannot_be_added_to_the_cart(): void
    {
        $buyer = User::factory()->create();
        $breedingOnly = $this->animal('breeding');
        $sale = $this->animal('sale');

        $this->actingAs($buyer)->post(route('cart.store', $breedingOnly));
        $this->actingAs($buyer)->post(route('cart.store', $sale));

        $this->assertSame([$sale->id], array_keys(session('cart', [])));
    }

    public function test_breeding_only_detail_page_offers_a_request_instead_of_add_to_cart(): void
    {
        $buyer = User::factory()->create();
        $breedingOnly = $this->animal('breeding', attributes: ['breeding_fee' => 90]);

        $this->actingAs($buyer)->get(route('animals.show', $breedingOnly))
            ->assertOk()
            ->assertSee('Request breeding')
            ->assertSee('₹90')
            ->assertDontSee('Add to Cart');
    }

    public function test_seller_can_create_a_breeding_only_listing_without_price_or_stock(): void
    {
        $seller = User::factory()->seller()->create();
        $category = Category::factory()->create();

        $this->actingAs($seller)->post(route('seller.animals.store'), [
            'name' => 'Champion Stud',
            'category_id' => $category->id,
            'gender' => 'male',
            'listing_type' => 'breeding',
            'breeding_fee' => '250',
            'state' => 'Punjab',
            'city' => 'Ludhiana',
            'is_active' => '1',
        ])->assertRedirect(route('seller.animals.index'));

        $animal = Animal::where('name', 'Champion Stud')->firstOrFail();
        $this->assertSame('breeding', $animal->listing_type);
        $this->assertEquals(250, $animal->breeding_fee);
        $this->assertEquals(0, $animal->price);
        $this->assertSame(1, $animal->stock);
        $this->assertSame('Ludhiana', $animal->location);
        $this->assertSame('Punjab', $animal->state);
        $this->assertEqualsWithDelta(30.9, $animal->latitude, 0.1);
    }

    public function test_breeding_only_listing_needs_a_fee_and_sale_listing_still_needs_a_price(): void
    {
        $seller = User::factory()->seller()->create();
        $place = ['state' => 'Punjab', 'city' => 'Ludhiana'];

        $this->actingAs($seller)->post(route('seller.animals.store'), ['name' => 'No Fee', 'gender' => 'male', 'listing_type' => 'breeding', ...$place])
            ->assertSessionHasErrors('breeding_fee');

        $this->actingAs($seller)->post(route('seller.animals.store'), ['name' => 'No Price', 'gender' => 'male', 'listing_type' => 'sale', 'stock' => 1, ...$place])
            ->assertSessionHasErrors('price');

        // A form that never sends listing_type keeps working as a normal sale listing.
        $this->actingAs($seller)->post(route('seller.animals.store'), ['name' => 'Plain Sale', 'gender' => 'male', 'price' => 100, 'stock' => 2, ...$place])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('sale', Animal::where('name', 'Plain Sale')->firstOrFail()->listing_type);
    }

    public function test_an_animal_needs_a_real_city_from_the_list(): void
    {
        $seller = User::factory()->seller()->create();

        $this->actingAs($seller)->post(route('seller.animals.store'), ['name' => 'Nowhere', 'gender' => 'male', 'price' => 10, 'stock' => 1, 'state' => 'Punjab', 'city' => 'Atlantis'])
            ->assertSessionHasErrors('city');
        $this->actingAs($seller)->post(route('seller.animals.store'), ['name' => 'No place', 'gender' => 'male', 'price' => 10, 'stock' => 1])
            ->assertSessionHasErrors(['state', 'city']);
    }
}
