<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The app has no browser bar, so every inner page carries its own visible back arrow. */
class BackButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_inner_pages_have_a_back_arrow_but_the_home_page_and_login_do_not(): void
    {
        foreach (['/vets', '/caretakers', '/breeding', '/breeding/match', '/about', '/contact', '/compare'] as $url) {
            $this->get($url)->assertOk()->assertSee('data-back=', false)->assertSee('aria-label="Back"', false);
        }

        foreach (['/home', '/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('data-back=', false);
        }
    }

    public function test_the_arrow_falls_back_to_the_home_page_when_there_is_nothing_to_go_back_to(): void
    {
        $this->get('/vets')->assertSee('data-back="'.route('home').'"', false)->assertDontSee('data-explicit', false);
    }

    public function test_panel_pages_carry_the_arrow_and_say_where_back_goes(): void
    {
        $seller = User::factory()->seller()->create();
        $buyer = User::factory()->create();
        $animal = Animal::factory()->create(['user_id' => $seller->id]);
        $order = Order::create(['user_id' => $buyer->id, 'total' => 100, 'status' => 'pending', 'shipping_name' => 'B', 'shipping_address' => 'Farm road']);
        OrderItem::create(['order_id' => $order->id, 'animal_id' => $animal->id, 'animal_name' => $animal->name, 'seller_id' => $seller->id, 'quantity' => 1, 'price' => 100, 'status' => 'pending']);

        // A page that names its own "back" always goes there (from an order: My Orders, not the checkout it came from).
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk()
            ->assertSee('data-back="'.route('orders.index').'"', false)->assertSee('data-explicit', false);
        $this->actingAs($seller)->get(route('seller.animals.create'))->assertOk()
            ->assertSee('data-back="'.route('seller.animals.index').'"', false);
        $this->actingAs($seller)->get(route('seller.animals.edit', $animal))->assertOk()
            ->assertSee('data-back="'.route('seller.animals.index').'"', false);

        // The panels' own home pages have no arrow.
        $this->actingAs($seller)->get(route('seller.dashboard'))->assertOk()->assertDontSee('data-back=', false);
        $this->actingAs($buyer)->get(route('account.dashboard'))->assertOk()->assertDontSee('data-back=', false);
    }

    public function test_admin_pages_carry_the_arrow(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('data-back=', false);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('data-back=', false);
    }
}
