<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Advertisement landing pages: the links shared on Facebook, Instagram and WhatsApp. */
class PromoPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_every_ad_page_opens_with_its_own_share_picture_in_both_languages(): void
    {
        foreach (['/promo' => 'marketplace', '/promo/doctor' => 'doctor', '/promo/sell' => 'sell', '/promo/breeding' => 'breeding'] as $url => $theme) {
            $this->get($url)->assertOk()
                ->assertSee('<meta property="og:image" content="'.asset("images/social/{$theme}-en.png").'">', false)
                ->assertSee('<meta property="og:url" content="'.url($url).'">', false);
            $this->assertFileExists(public_path("images/social/{$theme}-en.png"));
            $this->assertFileExists(public_path("images/social/{$theme}-hi.png"));
        }

        $this->get('/language/hi');
        $this->get('/promo/doctor')->assertOk()->assertSee('अपने पास पशु डॉक्टर खोजें')
            ->assertSee(asset('images/social/doctor-hi.png'), false);
    }

    public function test_the_main_button_goes_to_the_right_place(): void
    {
        $this->get('/promo')->assertSee('href="'.route('register').'"', false);
        $this->get('/promo/doctor')->assertSee('href="'.route('vets.index').'"', false);
        $this->get('/promo/breeding')->assertSee('href="'.route('breeding.browse').'"', false);
        $this->get('/promo/sell')->assertSee('href="'.route('register', ['as' => 'seller']).'"', false);
    }

    public function test_the_sell_link_opens_sign_up_with_seller_chosen(): void
    {
        $this->get('/register?as=seller')->assertOk()->assertSee('value="seller" class="mt-1 border-stone-300 text-amber-600 focus:ring-amber-500" checked', false);
        $this->get('/register')->assertOk()->assertSee('value="buyer" class="mt-1 border-stone-300 text-amber-600 focus:ring-amber-500" checked', false);
    }

    public function test_the_page_has_share_buttons_and_unknown_themes_are_not_found(): void
    {
        $this->get('/promo/sell')->assertSee('wa.me/?text=', false)->assertSee('facebook.com/sharer', false);
        $this->get('/promo/nothing')->assertNotFound();
    }

    public function test_other_pages_also_show_a_picture_when_shared(): void
    {
        $this->get('/vets')->assertSee('<meta property="og:image" content="'.asset('images/social/marketplace-en.png').'">', false);
    }
}
