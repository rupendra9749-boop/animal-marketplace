<?php

namespace Tests\Feature;

use App\Mail\NewOrderForSeller;
use App\Mail\OrderDecision;
use App\Mail\OrderPlacedForBuyer;
use App\Mail\Transport\PhpMailTransport;
use App\Models\Animal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function seller(string $name, string $phone): User
    {
        return User::factory()->seller()->create(['name' => $name, 'phone' => $phone, 'email' => strtolower(str_replace(' ', '.', $name)).'@example.com']);
    }

    private function animalOf(User $seller, string $name, int $price = 1000, int $stock = 5): Animal
    {
        return Animal::factory()->create(['user_id' => $seller->id, 'name' => $name, 'price' => $price, 'stock' => $stock, 'listing_type' => 'sale']);
    }

    /** Buyer puts the given animals in the cart and checks out. */
    private function checkout(User $buyer, array $quantities): Order
    {
        $cart = [];
        foreach ($quantities as $animal => $qty) {
            $cart[$animal] = $qty;
        }

        $this->actingAs($buyer)->withSession(['cart' => $cart])
            ->post('/checkout', ['shipping_name' => 'Ravi Kumar', 'shipping_address' => 'Farm road, Ludhiana'])
            ->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_placing_an_order_emails_the_buyer_and_every_seller_and_shows_the_seller_details(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $sunil = $this->seller('Sunil Seller', '+91 98222 22222');
        $goat = $this->animalOf($anil, 'Goat One');
        $cow = $this->animalOf($sunil, 'Cow One', 5000);
        $buyer = User::factory()->create(['name' => 'Buyer Bob', 'email' => 'bob@example.com']);

        $order = $this->checkout($buyer, [$goat->id => 2, $cow->id => 1]);

        Mail::assertSent(OrderPlacedForBuyer::class, fn ($m) => $m->hasTo('bob@example.com'));
        Mail::assertSent(NewOrderForSeller::class, 2);
        Mail::assertSent(NewOrderForSeller::class, fn ($m) => $m->hasTo('anil.seller@example.com') && $m->items->count() === 1);
        Mail::assertSent(NewOrderForSeller::class, fn ($m) => $m->hasTo('sunil.seller@example.com'));

        // Every animal starts as "waiting for the seller".
        $this->assertSame(['pending', 'pending'], $order->items()->pluck('status')->all());
        $this->assertSame('pending', $order->status);

        // The buyer meets each seller's details on the order page.
        $page = $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk();
        $page->assertSee('Anil Seller')->assertSee('+91 98111 11111')->assertSee('anil.seller@example.com')
            ->assertSee('Sunil Seller')->assertSee('+91 98222 22222')->assertSee('Waiting for seller');
    }

    public function test_the_seller_sees_the_buyer_and_approves_and_the_buyer_is_emailed_the_sellers_details(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $goat = $this->animalOf($anil, 'Goat One', 2500);
        $buyer = User::factory()->create(['name' => 'Buyer Bob', 'phone' => '+91 97777 77777']);
        $order = $this->checkout($buyer, [$goat->id => 2]);

        $page = $this->actingAs($anil)->get('/seller/sales')->assertOk();
        $page->assertSee('Buyer Bob')->assertSee('+91 97777 77777')->assertSee('Farm road, Ludhiana')->assertSee('Approve order')->assertSee('₹5,000');

        $this->actingAs($anil)->post(route('seller.sales.approve', $order))->assertRedirect()->assertSessionHas('status');

        $item = $order->items()->firstOrFail();
        $this->assertSame('approved', $item->status);
        $this->assertNotNull($item->decided_at);
        $this->assertSame('processing', $order->fresh()->status);   // shown to people as "Approved"
        $this->assertSame(3, $goat->fresh()->stock);                // 5 - 2, nothing given back

        Mail::assertSent(OrderDecision::class, fn ($m) => $m->hasTo($buyer->email) && $m->decision === 'approved' && $m->seller->is($anil));
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertSee('Approved');

        // Deciding twice does nothing more.
        $this->actingAs($anil)->post(route('seller.sales.approve', $order))->assertRedirect();
        Mail::assertSent(OrderDecision::class, 1);
    }

    public function test_declining_puts_the_animals_back_on_sale_and_cancels_the_order(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $goat = $this->animalOf($anil, 'Goat One', 1000, 5);
        $buyer = User::factory()->create();
        $order = $this->checkout($buyer, [$goat->id => 3]);
        $this->assertSame(2, $goat->fresh()->stock);

        $this->actingAs($anil)->post(route('seller.sales.decline', $order))->assertRedirect();

        $this->assertSame('declined', $order->items()->firstOrFail()->status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $goat->fresh()->stock);
        Mail::assertSent(OrderDecision::class, fn ($m) => $m->decision === 'declined' && $m->hasTo($buyer->email));
    }

    public function test_an_order_with_two_sellers_waits_until_both_have_answered(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $sunil = $this->seller('Sunil Seller', '+91 98222 22222');
        $goat = $this->animalOf($anil, 'Goat One');
        $cow = $this->animalOf($sunil, 'Cow One');
        $order = $this->checkout(User::factory()->create(), [$goat->id => 1, $cow->id => 1]);

        $this->actingAs($anil)->post(route('seller.sales.approve', $order));
        $this->assertSame('pending', $order->fresh()->status, 'still waiting for the second seller');

        $this->actingAs($sunil)->post(route('seller.sales.decline', $order));
        $this->assertSame('processing', $order->fresh()->status, 'one approved, one declined: the order goes ahead with what was approved');
        Mail::assertSent(OrderDecision::class, 2);
    }

    public function test_a_seller_can_only_decide_their_own_animals(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $other = $this->seller('Other Seller', '+91 98333 33333');
        $order = $this->checkout(User::factory()->create(), [$this->animalOf($anil, 'Goat One')->id => 1]);

        $this->actingAs($other)->post(route('seller.sales.approve', $order))->assertRedirect();
        $this->assertSame('pending', $order->items()->firstOrFail()->status);

        // Even by passing someone else's id: only admins may decide for another seller.
        $this->actingAs($other)->post(route('seller.sales.approve', ['order' => $order, 'seller_id' => $anil->id]));
        $this->assertSame('pending', $order->items()->firstOrFail()->status);

        $this->actingAs($other)->get('/seller/sales')->assertDontSee('Goat One');
        Mail::assertNotSent(OrderDecision::class);
    }

    public function test_an_admin_can_decide_for_a_seller(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $order = $this->checkout(User::factory()->create(), [$this->animalOf($anil, 'Goat One')->id => 1]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertSee('Anil Seller')->assertSee('Approve');
        $this->actingAs($admin)->post(route('seller.sales.approve', ['order' => $order, 'seller_id' => $anil->id]))->assertRedirect();

        $this->assertSame('approved', $order->items()->firstOrFail()->status);
    }

    public function test_seller_revenue_counts_only_approved_animals(): void
    {
        Mail::fake();
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $goat = $this->animalOf($anil, 'Goat One', 1000);
        $cow = $this->animalOf($anil, 'Cow One', 7000);
        $buyer = User::factory()->create();
        $first = $this->checkout($buyer, [$goat->id => 1]);
        $second = $this->checkout($buyer, [$cow->id => 1]);

        $this->actingAs($anil)->get('/seller/dashboard')->assertSee('₹0')->assertSee('2')->assertSee('orders to approve');

        $this->actingAs($anil)->post(route('seller.sales.approve', $first));
        $this->actingAs($anil)->post(route('seller.sales.decline', $second));

        $this->actingAs($anil)->get('/seller/dashboard')->assertSee('₹1,000')->assertDontSee('₹8,000');
    }

    public function test_a_broken_mail_server_never_stops_an_order(): void
    {
        // Point the mailer at a port nothing listens on: sending fails, the order must still go through.
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $goat = $this->animalOf($anil, 'Goat One');

        $order = $this->checkout(User::factory()->create(), [$goat->id => 1]);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertSame(4, $goat->fresh()->stock);
    }

    public function test_the_phpmail_transport_is_available_and_splits_a_message_the_way_mail_needs(): void
    {
        $transport = app('mail.manager')->mailer('phpmail')->getSymfonyTransport();
        $this->assertInstanceOf(PhpMailTransport::class, $transport);

        [$subject, $headers, $body] = PhpMailTransport::prepare("From: A <a@x.in>\r\nTo: b@x.in\r\nSubject: Order #4 approved\r\nContent-Type: text/html; charset=utf-8\r\n\r\n<p>Hi</p>\r\n<p>Bye</p>");

        $this->assertSame('Order #4 approved', $subject);
        $this->assertStringContainsString('From: A <a@x.in>', $headers);
        $this->assertStringContainsString('Content-Type: text/html', $headers);
        $this->assertStringNotContainsString('To: b@x.in', $headers);
        $this->assertStringNotContainsString('Subject:', $headers);
        $this->assertSame("<p>Hi</p>\n<p>Bye</p>", $body);
    }

    public function test_the_emails_contain_the_details_people_need(): void
    {
        $anil = $this->seller('Anil Seller', '+91 98111 11111');
        $goat = $this->animalOf($anil, 'Goat One', 2500);
        $buyer = User::factory()->create(['name' => 'Buyer Bob', 'phone' => '+91 97777 77777']);
        Mail::fake();
        $order = $this->checkout($buyer, [$goat->id => 2]);
        $order->load(['buyer', 'items.seller']);

        $toSeller = (new NewOrderForSeller($order, $anil, $order->items))->render();
        $this->assertStringContainsString('Buyer Bob', $toSeller);
        $this->assertStringContainsString('+91 97777 77777', $toSeller);
        $this->assertStringContainsString('₹5,000', $toSeller);
        $this->assertStringContainsString('Farm road, Ludhiana', $toSeller);
        $this->assertStringContainsString(route('seller.sales.index'), $toSeller);

        $toBuyer = (new OrderPlacedForBuyer($order))->render();
        $this->assertStringContainsString('Anil Seller', $toBuyer);
        $this->assertStringContainsString('+91 98111 11111', $toBuyer);
        $this->assertStringContainsString('anil.seller@example.com', $toBuyer);

        $approved = (new OrderDecision($order, $anil, $order->items, OrderItem::APPROVED))->render();
        $this->assertStringContainsString('approved your order', $approved);
        $this->assertStringContainsString('+91 98111 11111', $approved);

        $declined = (new OrderDecision($order, $anil, $order->items, OrderItem::DECLINED))->render();
        $this->assertStringContainsString('could not accept', $declined);
        $this->assertStringNotContainsString('+91 98111 11111', $declined, 'no contact details are needed for a declined order');
    }
}
