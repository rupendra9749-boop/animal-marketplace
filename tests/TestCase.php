<?php

namespace Tests;

use App\Support\Locations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Makes the next requests come from a visitor who picked this city (as the location bar does). */
    protected function fromCity(string $city, ?string $state = null): static
    {
        return $this->withSession(['location' => Locations::findByCity($city, $state)]);
    }
}
