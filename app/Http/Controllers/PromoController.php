<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Advertisement landing pages: the link people click from a Facebook / Instagram / WhatsApp post.
 * /promo is the general page; /promo/doctor, /promo/sell and /promo/breeding match the other three ad pictures.
 * Each one has its own share picture (public/images/social/{theme}-{en|hi}.png) and one main button.
 */
class PromoController extends Controller
{
    public const THEMES = ['marketplace', 'doctor', 'sell', 'breeding'];

    public function show(string $theme = 'marketplace'): View
    {
        abort_unless(in_array($theme, self::THEMES, true), 404);

        return view('promo.show', [
            'theme' => $theme,
            'page' => $this->page($theme),
            'others' => array_values(array_diff(self::THEMES, [$theme])),
        ]);
    }

    /** @return array<string, mixed> */
    private function page(string $theme): array
    {
        return match ($theme) {
            'doctor' => [
                'badge' => '🩺 '.__('Animal doctors'),
                'title' => __('Find an animal doctor near you'),
                'highlight' => __('animal doctor'),
                'sub' => __('Vets within 50 km of your city — call or WhatsApp in one tap.'),
                'features' => [
                    ['🐄', __('Every animal'), __('Doctors for cows, buffaloes, goats, dogs, cats, birds and horses.')],
                    ['🚨', __('Emergency & home visit'), __('Filter for 24x7 emergency and doctors who come to your home or farm.')],
                    ['📞', __('Call in one tap'), __('Phone and WhatsApp buttons on every doctor profile.')],
                ],
                'cta' => [__('Find a doctor now'), route('vets.index')],
                'animals' => ['dog', 'cow', 'cat', 'goat'],
            ],
            'sell' => [
                'badge' => '💰 '.__('For sellers'),
                'title' => __('Sell your animal — free'),
                'highlight' => __('free'),
                'sub' => __('Post in 2 minutes. Buyers near you call you directly.'),
                'features' => [
                    ['🆓', __('Free listing'), __('No listing fee and no commission on your sale.')],
                    ['📍', __('Buyers near you'), __('Your animal is shown to buyers within 250 km.')],
                    ['🩺', __('More ways to earn'), __('The same account can list breeding animals and a doctor or caretaker profile.')],
                ],
                'cta' => [__('Start selling free'), route('register', ['as' => 'seller'])],
                'animals' => ['cow', 'goat', 'buffalo', 'sheep'],
            ],
            'breeding' => [
                'badge' => '🧬 '.__('Breeding & care'),
                'title' => __('Breeding partners & caretakers near you'),
                'highlight' => __('caretakers'),
                'sub' => __('Find a breeding animal, check the match, or hire someone to look after your animals.'),
                'features' => [
                    ['🧬', __('Breeding match check'), __('Pick two animals and see how well they match before you breed them.')],
                    ['🐾', __('Breeding animals near you'), __('Studs and females offered for breeding within 250 km.')],
                    ['🤝', __('Caretakers'), __('People within 50 km for feeding, milking, walking and daily care.')],
                ],
                'cta' => [__('See breeding animals'), route('breeding.browse')],
                'animals' => ['dog', 'horse', 'cow', 'sheep'],
            ],
            default => [
                'badge' => '🐾 '.__('Now live across India'),
                'title' => __('Buy & sell healthy animals near you'),
                'highlight' => __('near you'),
                'sub' => __('Cows, buffaloes, goats, dogs, birds & more — directly from sellers in your area.'),
                'features' => [
                    ['📍', __('Animals near you'), __('Listings within 250 km with price, breed, age and vaccination.')],
                    ['📞', __('Talk to the seller directly'), __('After you order you get the seller’s name, phone and city.')],
                    ['🆓', __('Free to use'), __('Free for buyers and free to sell. No middlemen.')],
                ],
                'cta' => [__('Sign up free'), route('register')],
                'animals' => ['cow', 'buffalo', 'goat', 'dog', 'bird', 'cat'],
            ],
        };
    }
}
