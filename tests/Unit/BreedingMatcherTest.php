<?php

namespace Tests\Unit;

use App\Models\Animal;
use App\Models\Category;
use App\Services\BreedingMatcher;
use Tests\TestCase;

class BreedingMatcherTest extends TestCase
{
    private static int $nextId = 1;

    private function animal(string $type, array $attrs = []): Animal
    {
        $animal = new Animal(array_merge([
            'user_id' => 1, 'name' => 'Animal '.self::$nextId, 'breed' => 'Labrador', 'age' => '3 years',
            'gender' => 'male', 'weight' => '30 kg', 'is_vaccinated' => true, 'stock' => 1,
            'location' => 'Delhi', 'latitude' => 28.7041, 'longitude' => 77.1025,
        ], $attrs));
        $animal->id = self::$nextId++;
        $animal->setRelation('category', new Category(['name' => $type]));

        return $animal;
    }

    private function check(array $result, string $labelStart): ?array
    {
        foreach ($result['checks'] as $check) {
            if (str_starts_with($check['label'], $labelStart)) {
                return $check;
            }
        }

        return null;
    }

    public function test_healthy_same_breed_pair_is_an_excellent_match(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Dog', ['name' => 'Rex', 'gender' => 'male']),
            $this->animal('Dog', ['name' => 'Bella', 'gender' => 'female', 'age' => '2 years', 'weight' => '27 kg']),
        );

        $this->assertTrue($result['possible']);
        $this->assertSame('excellent', $result['level']);
        $this->assertGreaterThanOrEqual(85, $result['score']);
        $this->assertSame('Rex', $result['sire']->name);
        $this->assertSame('Bella', $result['dam']->name);
        $this->assertSame('Purebred', $result['offspring']['type']);
        $this->assertSame('Labrador puppies', $result['offspring']['name']);
    }

    public function test_pair_order_does_not_matter(): void
    {
        $male = $this->animal('Dog', ['gender' => 'male']);
        $female = $this->animal('Dog', ['gender' => 'female', 'age' => '2 years']);

        $ab = (new BreedingMatcher)->evaluate($male, $female);
        $ba = (new BreedingMatcher)->evaluate($female, $male);

        $this->assertSame($ab['score'], $ba['score']);
        $this->assertSame($male->id, $ba['sire']->id);
    }

    public function test_two_males_can_not_breed(): void
    {
        $result = (new BreedingMatcher)->evaluate($this->animal('Dog'), $this->animal('Dog'));

        $this->assertFalse($result['possible']);
        $this->assertSame('impossible', $result['level']);
        $this->assertSame(0, $result['score']);
        $this->assertNull($result['offspring']);
    }

    public function test_different_animal_types_can_not_breed(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Dog', ['gender' => 'male']),
            $this->animal('Cow', ['gender' => 'female']),
        );

        $this->assertFalse($result['possible']);
        $this->assertSame('fail', $this->check($result, 'Animal type')['status']);
    }

    public function test_animal_that_is_too_young_is_not_recommended_and_reports_the_wait(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Cat', ['breed' => 'Persian', 'gender' => 'female', 'age' => '4 months', 'weight' => '2 kg']),
            $this->animal('Cat', ['breed' => 'Persian', 'gender' => 'male', 'age' => '2 years', 'weight' => '4 kg']),
        );

        $this->assertTrue($result['possible']);
        $this->assertSame('poor', $result['level']);
        $this->assertLessThan(40, $result['score']);
        $this->assertSame(8, $result['ready_in_months']); // cats: 12 months minimum, this one is 4
        $ageFailures = array_filter($result['checks'], fn ($c) => str_starts_with($c['label'], 'Age') && $c['status'] === 'fail');
        $this->assertCount(1, $ageFailures); // only the kitten is flagged, not the adult
    }

    public function test_crossbreed_is_flagged_and_named(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Dog', ['breed' => 'Labrador', 'gender' => 'male']),
            $this->animal('Dog', ['breed' => 'Beagle', 'gender' => 'female', 'age' => '2 years', 'weight' => '20 kg']),
        );

        $this->assertSame('warn', $this->check($result, 'Breed')['status']);
        $this->assertSame('Crossbreed', $result['offspring']['type']);
        $this->assertSame('Labrador × Beagle puppies', $result['offspring']['name']);
    }

    public function test_unvaccinated_animals_lower_the_score(): void
    {
        $matcher = new BreedingMatcher;
        $good = $matcher->evaluate($this->animal('Dog'), $this->animal('Dog', ['gender' => 'female']));
        $bad = $matcher->evaluate($this->animal('Dog', ['is_vaccinated' => false]), $this->animal('Dog', ['gender' => 'female', 'is_vaccinated' => false]));

        $this->assertSame(16, $good['score'] - $bad['score']);
        $this->assertSame('warn', $this->check($bad, 'Vaccination')['status']);
    }

    public function test_big_size_difference_is_flagged_for_dogs_but_not_for_cows(): void
    {
        $matcher = new BreedingMatcher;
        $dogs = $matcher->evaluate(
            $this->animal('Dog', ['gender' => 'male', 'weight' => '45 kg']),
            $this->animal('Dog', ['gender' => 'female', 'weight' => '10 kg']),
        );
        $cows = $matcher->evaluate(
            $this->animal('Cow', ['gender' => 'male', 'weight' => '900 kg']),
            $this->animal('Cow', ['gender' => 'female', 'weight' => '300 kg']),
        );

        $this->assertSame('warn', $this->check($dogs, 'Size')['status']);
        $this->assertNull($this->check($cows, 'Size'));
    }

    public function test_far_apart_animals_get_a_transport_note(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Dog', ['gender' => 'male']),
            $this->animal('Dog', ['gender' => 'female', 'age' => '2 years', 'latitude' => 12.9716, 'longitude' => 77.5946]), // Bengaluru
        );

        $check = $this->check($result, 'Distance');
        $this->assertSame('warn', $check['status']);
        $this->assertStringContainsString('transport', $check['text']);
    }

    public function test_birds_with_unknown_sex_get_a_warning_not_a_failure(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Bird', ['gender' => 'unknown', 'breed' => 'African Grey', 'age' => '2 years', 'weight' => '0.5 kg']),
            $this->animal('Bird', ['gender' => 'unknown', 'breed' => 'African Grey', 'age' => '3 years', 'weight' => '0.5 kg']),
        );

        $this->assertTrue($result['possible']);
        $this->assertSame('warn', $this->check($result, 'Sex')['status']);
        $this->assertNull($result['sire']);
    }

    public function test_unknown_animal_type_uses_the_default_rules(): void
    {
        $result = (new BreedingMatcher)->evaluate(
            $this->animal('Rabbit', ['breed' => 'Dutch', 'gender' => 'male', 'age' => '2 years']),
            $this->animal('Rabbit', ['breed' => 'Dutch', 'gender' => 'female', 'age' => '2 years']),
        );

        $this->assertTrue($result['possible']);
        $this->assertSame('Dutch young', $result['offspring']['name']);
    }

    public function test_partner_suggestions_are_ranked_and_exclude_impossible_matches(): void
    {
        $subject = $this->animal('Dog', ['name' => 'Rex', 'gender' => 'male']);
        $sameBreed = $this->animal('Dog', ['name' => 'Same breed', 'gender' => 'female', 'age' => '2 years']);
        $crossbreed = $this->animal('Dog', ['name' => 'Cross', 'gender' => 'female', 'age' => '2 years', 'breed' => 'Beagle']);
        $sameSex = $this->animal('Dog', ['name' => 'Another male', 'gender' => 'male']);
        $cow = $this->animal('Cow', ['name' => 'Cow', 'gender' => 'female']);

        $suggestions = (new BreedingMatcher)->suggestPartners($subject, collect([$subject, $cow, $sameSex, $crossbreed, $sameBreed]));

        $this->assertSame(['Same breed', 'Cross'], $suggestions->map(fn ($s) => $s['animal']->name)->all());
    }

    public function test_age_text_is_parsed_in_common_formats(): void
    {
        $this->assertSame(3.0, BreedingMatcher::ageInMonths('3 months'));
        $this->assertSame(24.0, BreedingMatcher::ageInMonths('2 years'));
        $this->assertSame(12.0, BreedingMatcher::ageInMonths('1 year'));
        $this->assertSame(18.0, BreedingMatcher::ageInMonths('1y 6m'));
        $this->assertSame(30.0, BreedingMatcher::ageInMonths('2.5 yrs'));
        $this->assertEqualsWithDelta(1.8, BreedingMatcher::ageInMonths('8 weeks'), 0.05);
        $this->assertNull(BreedingMatcher::ageInMonths('adult'));
        $this->assertNull(BreedingMatcher::ageInMonths(null));
        $this->assertNull(BreedingMatcher::ageInMonths(''));
    }

    public function test_weight_text_is_parsed_in_common_formats(): void
    {
        $this->assertSame(450.0, BreedingMatcher::weightInKg('450 kg'));
        $this->assertSame(0.5, BreedingMatcher::weightInKg('500 g'));
        $this->assertEqualsWithDelta(27.2, BreedingMatcher::weightInKg('60 lb'), 0.1);
        $this->assertSame(8.0, BreedingMatcher::weightInKg('8'));
        $this->assertNull(BreedingMatcher::weightInKg('heavy'));
        $this->assertNull(BreedingMatcher::weightInKg(null));
    }
}
