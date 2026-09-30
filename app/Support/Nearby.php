<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * "Near me" rules shared by every search: where the visitor is, how far each kind of listing may be,
 * and paging a distance-sorted collection.
 */
class Nearby
{
    /** Animals and breeding animals are shown within this many km of the visitor. */
    public const ANIMAL_RADIUS_KM = 250;

    public const BREEDING_RADIUS_KM = 250;

    /** Doctors and caretakers are local services, so they are shown within this many km. */
    public const VET_RADIUS_KM = 50;

    public const CARETAKER_RADIUS_KM = 50;

    /**
     * Where the visitor is: a city they picked this session, otherwise the city on their profile.
     *
     * @return array{state: string, city: string, lat: float, lng: float}|null
     */
    public static function current(): ?array
    {
        $picked = session('location');
        if (is_array($picked) && isset($picked['lat'], $picked['lng'], $picked['city'])) {
            return $picked;
        }

        $user = auth()->user();
        if ($user && $user->city && $user->latitude !== null && $user->longitude !== null) {
            return ['state' => (string) $user->state, 'city' => $user->city, 'lat' => (float) $user->latitude, 'lng' => (float) $user->longitude];
        }

        return null;
    }

    /**
     * The location a page should search around: an explicit ?state=&city= link (like a "doctors in Ludhiana"
     * chip) wins, otherwise the visitor's own location.
     *
     * @return array{state: string, city: string, lat: float, lng: float}|null
     */
    public static function resolve(Request $request): ?array
    {
        $explicit = Locations::find($request->query('state'), $request->query('city'));

        return $explicit ?? self::current();
    }

    /** Remember a city for this visitor (guests and logged-in users alike). */
    public static function remember(array $location): void
    {
        session(['location' => [
            'state' => $location['state'],
            'city' => $location['city'],
            'lat' => (float) $location['lat'],
            'lng' => (float) $location['lng'],
        ]]);
    }

    /** @param  Collection<int, mixed>  $items */
    public static function paginate(Collection $items, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
