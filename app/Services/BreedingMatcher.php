<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Scores how good a breeding pair two animal listings would make.
 *
 * Guidance only - it works from what sellers typed into the listings and the rules in
 * config/breeding.php, and is not a substitute for a vet.
 */
class BreedingMatcher
{
    public const NEARBY_KM = 250;

    /**
     * @return array{
     *   possible: bool, score: int, level: string, verdict: string, summary: string,
     *   sire: ?Animal, dam: ?Animal, checks: array<int, array{status: string, label: string, text: string}>,
     *   offspring: ?array{name: string, type: string, gestation: ?string, litter: ?string},
     *   ready_in_months: ?int
     * }
     */
    public function evaluate(Animal $a, Animal $b): array
    {
        $checks = [];
        $score = 100;

        $slugA = $this->speciesSlug($a);
        $slugB = $this->speciesSlug($b);

        // 1. Species - different animal types can never be bred together.
        if ($slugA !== null && $slugB !== null && $slugA !== $slugB) {
            return $this->impossible(
                $a, $b,
                [['status' => 'fail', 'label' => __('Animal type'), 'text' => __(':a is a :ta and :b is a :tb - different animals cannot be bred together.', [
                    'a' => $a->name, 'ta' => $this->speciesLabel($a), 'b' => $b->name, 'tb' => $this->speciesLabel($b),
                ])]],
                __('Different animal types cannot be bred together.')
            );
        }

        $rules = $this->rules($slugA ?? $slugB);
        if ($slugA === null || $slugB === null) {
            $checks[] = ['status' => 'warn', 'label' => __('Animal type'), 'text' => __('The animal type is missing on one listing, so general guidance is used.')];
            $score -= 10;
        } else {
            $checks[] = ['status' => 'pass', 'label' => __('Animal type'), 'text' => __('Both are :type - a valid pairing.', ['type' => Str::lower($rules['label'])])];
        }

        // 2. Sex - need one male and one female.
        $sexA = $a->gender;
        $sexB = $b->gender;
        $sire = $dam = null;

        if ($sexA !== 'unknown' && $sexA === $sexB) {
            return $this->impossible(
                $a, $b,
                array_merge($checks, [['status' => 'fail', 'label' => __('Sex'), 'text' => __('Both animals are :sex - breeding needs one male and one female.', ['sex' => $sexA])]]),
                __('Two :sex animals cannot breed together.', ['sex' => $sexA])
            );
        }

        if ($sexA !== 'unknown' && $sexB !== 'unknown') {
            [$sire, $dam] = $sexA === 'male' ? [$a, $b] : [$b, $a];
            $checks[] = ['status' => 'pass', 'label' => __('Sex'), 'text' => __('One male and one female - a valid pairing.')];
        } else {
            $checks[] = ['status' => 'warn', 'label' => __('Sex'), 'text' => __('The sex of at least one animal is not confirmed. Confirm it (DNA sexing for birds) before going ahead.')];
            $score -= 12;
        }

        // 3. Breed
        $breedA = $this->normaliseBreed($a->breed);
        $breedB = $this->normaliseBreed($b->breed);
        $crossbreed = false;
        if ($breedA !== null && $breedA === $breedB) {
            $checks[] = ['status' => 'pass', 'label' => __('Breed'), 'text' => __('Both are :breed - expect purebred offspring.', ['breed' => $a->breed])];
        } elseif ($breedA !== null && $breedB !== null) {
            $crossbreed = true;
            $score -= 10;
            $checks[] = ['status' => 'warn', 'label' => __('Breed'), 'text' => __(':a × :b is a crossbreed - the offspring\'s size and traits can vary.', ['a' => $a->breed, 'b' => $b->breed])];
        } else {
            $score -= 4;
            $checks[] = ['status' => 'info', 'label' => __('Breed'), 'text' => __('The breed is missing on one listing, so offspring type cannot be predicted.')];
        }

        // 4. Age - each animal against the minimum and maximum breeding age.
        $readyIn = 0;
        $tooOldOrYoung = false;
        foreach ([$a, $b] as $animal) {
            $months = self::ageInMonths($animal->age);
            $sex = in_array($animal->gender, ['male', 'female'], true) ? $animal->gender : null;
            $min = $sex ? $rules['min_age'][$sex] : max($rules['min_age']);
            $max = $sex ? $rules['max_age'][$sex] : max($rules['max_age']);

            if ($months === null) {
                $score -= 8;
                $checks[] = ['status' => 'warn', 'label' => __('Age: :name', ['name' => $animal->name]), 'text' => __('Age is not listed - ask the seller.')];
            } elseif ($months < $min) {
                $wait = (int) ceil($min - $months);
                $readyIn = max($readyIn, $wait);
                $tooOldOrYoung = true;
                $score -= 35;
                $checks[] = ['status' => 'fail', 'label' => __('Age: :name', ['name' => $animal->name]), 'text' => __('Too young to breed safely (:age). Wait about :n more month(s).', ['age' => $animal->age, 'n' => $wait])];
            } elseif ($months > $max) {
                $tooOldOrYoung = true;
                $score -= 35;
                $checks[] = ['status' => 'fail', 'label' => __('Age: :name', ['name' => $animal->name]), 'text' => __('Past the usual breeding age (:age). Breeding now carries higher risk.', ['age' => $animal->age])];
            } else {
                $checks[] = ['status' => 'pass', 'label' => __('Age: :name', ['name' => $animal->name]), 'text' => __(':age - within the healthy breeding age.', ['age' => $animal->age])];
            }
        }

        // 5. Health - vaccination
        $unvaccinated = collect([$a, $b])->reject(fn (Animal $x) => $x->is_vaccinated);
        if ($unvaccinated->isEmpty()) {
            $checks[] = ['status' => 'pass', 'label' => __('Vaccination'), 'text' => __('Both animals are vaccinated.')];
        } else {
            $score -= 8 * $unvaccinated->count();
            $names = $unvaccinated->pluck('name')->join(__(' and '));
            $checks[] = ['status' => 'warn', 'label' => __('Vaccination'), 'text' => $unvaccinated->count() > 1
                ? __(':names are not vaccinated - get them vaccinated and vet-checked before breeding.', ['names' => $names])
                : __(':names is not vaccinated - get it vaccinated and vet-checked before breeding.', ['names' => $names])];
        }

        // 6. Size - a much bigger male can hurt a smaller female.
        if ($rules['check_size'] && $sire && $dam) {
            $wSire = self::weightInKg($sire->weight);
            $wDam = self::weightInKg($dam->weight);
            if ($wSire && $wDam) {
                $ratio = $wSire / $wDam;
                if ($ratio > 1.6 || $ratio < 0.6) {
                    $score -= 10;
                    $checks[] = ['status' => 'warn', 'label' => __('Size'), 'text' => __('Big size difference (:s kg vs :d kg) - can make mating or birth difficult.', ['s' => round($wSire, 1), 'd' => round($wDam, 1)])];
                } else {
                    $checks[] = ['status' => 'pass', 'label' => __('Size'), 'text' => __('Similar size (:s kg and :d kg).', ['s' => round($wSire, 1), 'd' => round($wDam, 1)])];
                }
            }
        }

        // 7. Distance
        if ($a->latitude !== null && $b->latitude !== null) {
            $km = (int) round($a->distanceFrom($b->latitude, $b->longitude));
            if ($km <= self::NEARBY_KM) {
                $checks[] = ['status' => 'pass', 'label' => __('Distance'), 'text' => $km <= 20
                    ? __('Both are in the same area (:km km apart) - easy to arrange.', ['km' => $km])
                    : __(':km km apart - close enough to arrange a visit.', ['km' => $km])];
            } else {
                $score -= 8;
                $checks[] = ['status' => 'warn', 'label' => __('Distance'), 'text' => __(':km km apart - you will need to arrange transport.', ['km' => $km])];
            }
        } else {
            $checks[] = ['status' => 'info', 'label' => __('Distance'), 'text' => __('Location is missing on one listing.')];
        }

        // 8. Availability & sellers
        if ($a->stock < 1 || $b->stock < 1) {
            $score -= 5;
            $checks[] = ['status' => 'warn', 'label' => __('Availability'), 'text' => __('One of these animals is sold out - ask the seller if it is still available.')];
        } elseif ($a->user_id === $b->user_id) {
            $checks[] = ['status' => 'pass', 'label' => __('Availability'), 'text' => __('Both are available from the same seller - one conversation to arrange it.')];
        } else {
            $checks[] = ['status' => 'info', 'label' => __('Availability'), 'text' => __('Available from two different sellers - contact both.')];
        }

        $score = max(5, min(100, $score));
        if ($tooOldOrYoung) {
            $score = min($score, 39);
        }

        [$level, $verdict, $summary] = $this->verdict($score, $readyIn);

        return [
            'possible' => true,
            'score' => $score,
            'level' => $level,
            'verdict' => $verdict,
            'summary' => $summary,
            'sire' => $sire,
            'dam' => $dam,
            'checks' => $checks,
            'offspring' => $this->offspring($a, $b, $rules, $crossbreed, $breedA !== null && $breedB !== null),
            'ready_in_months' => $readyIn ?: null,
        ];
    }

    /**
     * The best breeding partners for $animal among $candidates, best first.
     *
     * @param  Collection<int, Animal>  $candidates
     * @return Collection<int, array{animal: Animal, result: array}>
     */
    public function suggestPartners(Animal $animal, Collection $candidates, int $limit = 6): Collection
    {
        return $candidates
            ->reject(fn (Animal $other) => $other->id === $animal->id)
            ->map(fn (Animal $other) => ['animal' => $other, 'result' => $this->evaluate($animal, $other)])
            ->filter(fn (array $row) => $row['result']['possible'])
            ->sortByDesc(fn (array $row) => $row['result']['score'])
            ->take($limit)
            ->values();
    }

    /** "3 months", "2 years", "1y 6m", "8 weeks" -> months. Null when it cannot be read. */
    public static function ageInMonths(?string $age): ?float
    {
        if ($age === null || trim($age) === '') {
            return null;
        }

        $found = preg_match_all(
            '/(\d+(?:\.\d+)?)\s*(years?|yrs?|y|months?|mos?|m|weeks?|wks?|w|days?|d)\b/i',
            $age,
            $parts,
            PREG_SET_ORDER
        );

        if (! $found) {
            return null;
        }

        $months = 0.0;
        foreach ($parts as $part) {
            $n = (float) $part[1];
            $unit = Str::lower($part[2]);
            $months += match (true) {
                str_starts_with($unit, 'y') => $n * 12,
                str_starts_with($unit, 'm') => $n,
                str_starts_with($unit, 'w') => $n / 4.345,
                default => $n / 30.4,
            };
        }

        return round($months, 1);
    }

    /** "450 kg", "8 kg", "500 g", "60 lb" -> kilograms. Null when it cannot be read. */
    public static function weightInKg(?string $weight): ?float
    {
        if ($weight === null || ! preg_match('/(\d+(?:\.\d+)?)\s*(kgs?|gm?s?|lbs?)?/i', $weight, $m)) {
            return null;
        }

        $n = (float) $m[1];
        $unit = Str::lower($m[2] ?? 'kg');

        return match (true) {
            str_starts_with($unit, 'lb') => $n * 0.4536,
            str_starts_with($unit, 'g') => $n / 1000,
            default => $n,
        };
    }

    private function speciesSlug(Animal $animal): ?string
    {
        $name = $animal->category?->name;

        return $name ? Str::slug($name) : null;
    }

    private function speciesLabel(Animal $animal): string
    {
        return Str::lower(__($animal->category?->name ?? 'animal'));
    }

    /** @return array<string, mixed> */
    private function rules(?string $slug): array
    {
        return config("breeding.{$slug}") ?? config('breeding.default');
    }

    private function normaliseBreed(?string $breed): ?string
    {
        $breed = $breed !== null ? Str::lower(trim($breed)) : '';

        return $breed === '' ? null : $breed;
    }

    /** @return array{0: string, 1: string, 2: string} */
    private function verdict(int $score, int $readyIn): array
    {
        return match (true) {
            $score >= 85 => ['excellent', __('Excellent match'), __('These two make a strong breeding pair.')],
            $score >= 65 => ['good', __('Good match'), __('A good pairing - check the notes below.')],
            $score >= 40 => ['caution', __('Possible, with caution'), __('This can work, but a few things need attention first.')],
            default => ['poor', __('Not recommended yet'), $readyIn > 0
                ? __('Not ready yet - wait about :n month(s) and check again.', ['n' => $readyIn])
                : __('Not recommended right now - see the issues below.')],
        };
    }

    /** @return array<string, mixed> */
    private function impossible(Animal $a, Animal $b, array $checks, string $summary): array
    {
        return [
            'possible' => false,
            'score' => 0,
            'level' => 'impossible',
            'verdict' => __('Not possible'),
            'summary' => $summary,
            'sire' => null,
            'dam' => null,
            'checks' => $checks,
            'offspring' => null,
            'ready_in_months' => null,
        ];
    }

    /** @return array{name: string, type: string, gestation: ?string, litter: ?string} */
    private function offspring(Animal $a, Animal $b, array $rules, bool $crossbreed, bool $bothBreedsKnown): array
    {
        $young = $rules['young'];

        if ($crossbreed) {
            $name = "{$a->breed} × {$b->breed} {$young}";
            $type = __('Crossbreed');
        } elseif ($bothBreedsKnown) {
            $name = "{$a->breed} {$young}";
            $type = __('Purebred');
        } else {
            $name = ucfirst($young);
            $type = __('Breed not confirmed');
        }

        $gestation = isset($rules['gestation_days'])
            ? __(':days days (about :weeks weeks)', ['days' => $rules['gestation_days'], 'weeks' => round($rules['gestation_days'] / 7)])
            : ($rules['incubation'] ?? null);

        return ['name' => $name, 'type' => $type, 'gestation' => $gestation, 'litter' => $rules['litter']];
    }
}
