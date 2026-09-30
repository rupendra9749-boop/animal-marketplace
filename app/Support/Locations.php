<?php

namespace App\Support;

/**
 * Every Indian state / union territory with its cities and towns and their coordinates
 * (resources/data/india-locations.json, from the open countries-states-cities database).
 * Used by registration, listings, doctors and caretakers so location search works for all of India.
 */
class Locations
{
    public const COUNTRY = 'India';

    /**
     * Where the sample data and older listings put the 35 cities the site launched with
     * (a few names exist in several states, so they are pinned here).
     */
    private const LEGACY = [
        'Delhi' => 'Delhi', 'Mumbai' => 'Maharashtra', 'Bengaluru' => 'Karnataka', 'Hyderabad' => 'Telangana',
        'Chennai' => 'Tamil Nadu', 'Kolkata' => 'West Bengal', 'Pune' => 'Maharashtra', 'Ahmedabad' => 'Gujarat',
        'Surat' => 'Gujarat', 'Jaipur' => 'Rajasthan', 'Lucknow' => 'Uttar Pradesh', 'Kanpur' => 'Uttar Pradesh',
        'Nagpur' => 'Maharashtra', 'Indore' => 'Madhya Pradesh', 'Bhopal' => 'Madhya Pradesh', 'Patna' => 'Bihar',
        'Ludhiana' => 'Punjab', 'Agra' => 'Uttar Pradesh', 'Nashik' => 'Maharashtra', 'Faridabad' => 'Haryana',
        'Meerut' => 'Uttar Pradesh', 'Rajkot' => 'Gujarat', 'Varanasi' => 'Uttar Pradesh', 'Amritsar' => 'Punjab',
        'Chandigarh' => 'Chandigarh', 'Gurugram' => 'Haryana', 'Noida' => 'Uttar Pradesh', 'Coimbatore' => 'Tamil Nadu',
        'Kochi' => 'Kerala', 'Guwahati' => 'Assam', 'Ranchi' => 'Jharkhand', 'Jodhpur' => 'Rajasthan',
        'Raipur' => 'Chhattisgarh', 'Karnal' => 'Haryana', 'Hisar' => 'Haryana',
    ];

    /** Names the dataset spells differently. */
    private const ALIASES = ['Kochi' => 'Cochin'];

    /** @var list<array{name: string, type: string, lat: float|null, lng: float|null, cities: list<array{0: string, 1: float, 2: float}>}>|null */
    private static ?array $states = null;

    /** @return list<array{name: string, type: string, lat: float|null, lng: float|null, cities: list<array{0: string, 1: float, 2: float}>}> */
    private static function load(): array
    {
        return self::$states ??= json_decode((string) file_get_contents(resource_path('data/india-locations.json')), true)['states'];
    }

    /** @return list<string> */
    public static function states(): array
    {
        return array_column(self::load(), 'name');
    }

    /** @return list<string> city names of a state, alphabetical */
    public static function cities(string $state): array
    {
        foreach (self::load() as $row) {
            if (strcasecmp($row['name'], $state) === 0) {
                return array_column($row['cities'], 0);
            }
        }

        return [];
    }

    /** @return array{state: string, city: string, lat: float, lng: float}|null */
    public static function find(?string $state, ?string $city): ?array
    {
        if (! $state || ! $city) {
            return null;
        }

        foreach (self::load() as $row) {
            if (strcasecmp($row['name'], $state) !== 0) {
                continue;
            }
            foreach ($row['cities'] as [$name, $lat, $lng]) {
                if (strcasecmp($name, $city) === 0) {
                    return ['state' => $row['name'], 'city' => $name, 'lat' => (float) $lat, 'lng' => (float) $lng];
                }
            }
        }

        return null;
    }

    /**
     * Look a city up by name alone (older records stored only the city). The launch cities resolve to
     * their well-known state; any other name resolves to its first match.
     *
     * @return array{state: string, city: string, lat: float, lng: float}|null
     */
    public static function findByCity(?string $city, ?string $state = null): ?array
    {
        if (! $city) {
            return null;
        }

        if ($state) {
            return self::find($state, self::ALIASES[$city] ?? $city) ? self::withName(self::find($state, self::ALIASES[$city] ?? $city), $city) : null;
        }

        $pinned = self::LEGACY[$city] ?? null;
        if ($pinned) {
            $hit = self::find($pinned, self::ALIASES[$city] ?? $city);

            return $hit ? self::withName($hit, $city) : null;
        }

        foreach (self::load() as $row) {
            foreach ($row['cities'] as [$name, $lat, $lng]) {
                if (strcasecmp($name, $city) === 0) {
                    return ['state' => $row['name'], 'city' => $name, 'lat' => (float) $lat, 'lng' => (float) $lng];
                }
            }
        }

        return null;
    }

    /** The nearest known city to a GPS position. */
    public static function nearest(float $lat, float $lng): array
    {
        $best = null;

        foreach (self::load() as $row) {
            foreach ($row['cities'] as [$name, $cLat, $cLng]) {
                $km = self::distanceKm($lat, $lng, $cLat, $cLng);
                if ($best === null || $km < $best['km']) {
                    $best = ['state' => $row['name'], 'city' => $name, 'lat' => (float) $cLat, 'lng' => (float) $cLng, 'km' => $km];
                }
            }
        }

        return $best;
    }

    /** Straight-line (great-circle) distance in kilometres. */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** @return array{state: string, city: string, lat: float, lng: float} */
    private static function withName(array $hit, string $city): array
    {
        return [...$hit, 'city' => $city];
    }
}
