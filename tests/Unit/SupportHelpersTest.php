<?php

namespace Tests\Unit;

use App\Support\Locations;
use App\Support\Money;
use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class SupportHelpersTest extends TestCase
{
    public function test_rupees_use_indian_digit_grouping_and_hide_empty_decimals(): void
    {
        $this->assertSame('₹0', Money::inr(0));
        $this->assertSame('₹850', Money::inr(850));
        $this->assertSame('₹99,999', Money::inr(99999));
        $this->assertSame('₹1,00,000', Money::inr(100000));
        $this->assertSame('₹12,34,568', Money::inr(1234567.5));
        $this->assertSame('₹12,34,567.50', Money::inr(1234567.5, 2));
        $this->assertSame('₹1,440', Money::inr('1440.00', 2));
        $this->assertSame('₹1,500.50', Money::inr(1500.5, 2));
        $this->assertSame('-₹4,500.25', Money::inr(-4500.25, 2));
        $this->assertSame('₹0', Money::inr(null));
    }

    public function test_mobile_numbers_are_normalised_and_junk_is_rejected(): void
    {
        foreach (['9876543210', '+91 98765 43210', '+919876543210', '09876543210', '98765-43210', '91 9876543210'] as $typed) {
            $this->assertSame('+91 98765 43210', Phone::mobile($typed), $typed);
        }
        foreach (['12345', '5876543210', '98765 4321', 'call me', '', null, '+1 415 555 0100'] as $bad) {
            $this->assertNull(Phone::mobile($bad), (string) $bad);
        }
    }

    public function test_landlines_with_an_std_code_are_accepted_as_contact_numbers(): void
    {
        $this->assertSame('01612345678', Phone::contact('0161 2345678'));
        $this->assertSame('01123456789', Phone::contact('011-23456789'));
        $this->assertSame('+91 98765 43210', Phone::contact('9876543210'));
        $this->assertNull(Phone::contact('12345678'));
        $this->assertSame('+919876543210', Phone::forDialing('+91 98765 43210'));
        $this->assertSame('01612345678', Phone::forDialing('01612345678'));
    }

    public function test_every_state_and_union_territory_is_present_with_cities(): void
    {
        $states = Locations::states();

        $this->assertCount(36, $states);
        foreach (['Punjab', 'Kerala', 'Delhi', 'Ladakh', 'Lakshadweep', 'Andaman and Nicobar Islands', 'Uttar Pradesh'] as $state) {
            $this->assertContains($state, $states);
        }
        foreach ($states as $state) {
            $this->assertNotEmpty(Locations::cities($state), $state);
        }
    }

    public function test_cities_are_looked_up_by_state_and_by_name(): void
    {
        $this->assertSame('Punjab', Locations::find('punjab', 'ludhiana')['state']);
        $this->assertNull(Locations::find('Rajasthan', 'Ludhiana'));
        $this->assertNull(Locations::find(null, 'Ludhiana'));

        // The launch cities resolve to their well-known state, even when a name exists in several states.
        $this->assertSame('Rajasthan', Locations::findByCity('Jodhpur')['state']);
        $this->assertSame('Chhattisgarh', Locations::findByCity('Raipur')['state']);
        $this->assertSame('Kerala', Locations::findByCity('Kochi')['state']);
        $this->assertSame('Kochi', Locations::findByCity('Kochi')['city']);
    }

    public function test_nearest_city_and_distance(): void
    {
        $this->assertSame('Ludhiana', Locations::nearest(30.91, 75.85)['city']);
        $this->assertEqualsWithDelta(1150, Locations::distanceKm(28.6139, 77.2090, 19.0760, 72.8777), 40);
        $this->assertEqualsWithDelta(0, Locations::distanceKm(10, 20, 10, 20), 0.001);
    }
}
