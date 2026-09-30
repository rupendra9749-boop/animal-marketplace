<?php

namespace App\Models\Concerns;

use App\Support\Locations;
use Illuminate\Database\Eloquent\Builder;

/** For models with latitude / longitude columns: distance to a point and a cheap "roughly nearby" query. */
trait HasLocation
{
    /**
     * Distance in kilometres from the given coordinates, or null when this record has no position.
     */
    public function distanceFrom(float $lat, float $lng): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return Locations::distanceKm($lat, $lng, (float) $this->latitude, (float) $this->longitude);
    }

    /**
     * Narrows the query to the square around a point that contains the search circle, so the exact
     * distance only has to be worked out for a handful of rows instead of the whole table.
     */
    public function scopeWithinBox(Builder $query, float $lat, float $lng, float $km): Builder
    {
        $latSpan = $km / 111.0;
        $lngSpan = $km / (111.0 * max(cos(deg2rad($lat)), 0.2));

        return $query
            ->whereBetween('latitude', [$lat - $latSpan, $lat + $latSpan])
            ->whereBetween('longitude', [$lng - $lngSpan, $lng + $lngSpan]);
    }
}
