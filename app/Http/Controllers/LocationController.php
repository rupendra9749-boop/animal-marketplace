<?php

namespace App\Http\Controllers;

use App\Support\Locations;
use App\Support\Nearby;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /** Cities and towns of one state, for the city picker. */
    public function cities(Request $request): JsonResponse
    {
        $cities = Locations::cities((string) $request->query('state'));

        // ?hi=1 adds the Hindi spelling next to each name, so a Hindi reader can pick (and type) it in Devanagari.
        if ($request->boolean('hi')) {
            $cities = array_map(fn (string $city) => [$city, trans($city, [], 'hi')], $cities);
        }

        return response()->json($cities)->header('Cache-Control', 'public, max-age=86400');
    }

    /** The visitor picked a state and city. */
    public function select(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
        ], [
            'city.required' => __('Please pick your city from the list.'),
        ]);

        $hit = Locations::find($data['state'], $data['city']);

        if (! $hit) {
            return redirect($this->previousPage())->with('status', __('We could not find that city. Please pick it from the list.'));
        }

        Nearby::remember($hit);

        return redirect($this->previousPage())->with('status', __('Showing results near :city.', ['city' => __($hit['city'])]));
    }

    /** "Use my location": turn GPS coordinates into the nearest known city. */
    public function detect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $nearest = Locations::nearest((float) $data['lat'], (float) $data['lng']);

        // The list covers all of India, so a "nearest city" hundreds of km away means the phone is not in India.
        if ($nearest['km'] > 250) {
            return response()->json(['message' => __('You seem to be outside India. Please choose your city instead.')], 422);
        }

        Nearby::remember($nearest);

        return response()->json(['state' => $nearest['state'], 'city' => $nearest['city'], 'km' => round($nearest['km'])]);
    }

    /** Forget the picked city (falls back to the one on the profile). */
    public function clear(): RedirectResponse
    {
        session()->forget('location');

        return redirect($this->previousPage());
    }

    /** Where the person was, without any ?state=&city= override or page number that would hide the change. */
    private function previousPage(): string
    {
        $previous = url()->previous();
        $parts = parse_url($previous);

        if (! $parts || (isset($parts['host']) && $parts['host'] !== request()->getHost())) {
            return route('home');
        }

        parse_str($parts['query'] ?? '', $query);
        unset($query['state'], $query['city'], $query['page']);

        // A full address, because the app lives in a sub-folder (/marketplace) and the referrer's path already
        // contains it: a bare path would get the folder added a second time.
        return request()->getSchemeAndHttpHost().($parts['path'] ?? '/').($query ? '?'.http_build_query($query) : '');
    }
}
