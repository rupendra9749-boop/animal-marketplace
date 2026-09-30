<?php

/*
 * General breeding guidance per animal type (keyed by the category slug).
 * Ages are in months. This is guidance for buyers, not veterinary advice.
 */
return [
    'dog' => [
        'label' => 'Dog', 'young' => 'puppies',
        'min_age' => ['female' => 18, 'male' => 12], 'max_age' => ['female' => 96, 'male' => 120],
        'gestation_days' => 63, 'litter' => '4–8 puppies', 'check_size' => true,
    ],
    'cat' => [
        'label' => 'Cat', 'young' => 'kittens',
        'min_age' => ['female' => 12, 'male' => 12], 'max_age' => ['female' => 84, 'male' => 96],
        'gestation_days' => 65, 'litter' => '3–5 kittens', 'check_size' => true,
    ],
    'cow' => [
        'label' => 'Cow', 'young' => 'calves',
        'min_age' => ['female' => 15, 'male' => 15], 'max_age' => ['female' => 144, 'male' => 120],
        'gestation_days' => 283, 'litter' => '1 calf', 'check_size' => false,
    ],
    'buffalo' => [
        'label' => 'Buffalo', 'young' => 'calves',
        'min_age' => ['female' => 30, 'male' => 30], 'max_age' => ['female' => 180, 'male' => 144],
        'gestation_days' => 310, 'litter' => '1 calf', 'check_size' => false,
    ],
    'goat' => [
        'label' => 'Goat', 'young' => 'kids',
        'min_age' => ['female' => 10, 'male' => 10], 'max_age' => ['female' => 120, 'male' => 96],
        'gestation_days' => 150, 'litter' => '1–3 kids', 'check_size' => true,
    ],
    'sheep' => [
        'label' => 'Sheep', 'young' => 'lambs',
        'min_age' => ['female' => 12, 'male' => 12], 'max_age' => ['female' => 96, 'male' => 96],
        'gestation_days' => 147, 'litter' => '1–2 lambs', 'check_size' => true,
    ],
    'horse' => [
        'label' => 'Horse', 'young' => 'foals',
        'min_age' => ['female' => 36, 'male' => 36], 'max_age' => ['female' => 240, 'male' => 240],
        'gestation_days' => 340, 'litter' => '1 foal', 'check_size' => false,
    ],
    'bird' => [
        'label' => 'Bird', 'young' => 'chicks',
        'min_age' => ['female' => 12, 'male' => 12], 'max_age' => ['female' => 120, 'male' => 120],
        'gestation_days' => null, 'incubation' => '18–28 days incubation', 'litter' => '2–6 eggs per clutch', 'check_size' => false,
    ],

    // Used for any animal type not listed above.
    'default' => [
        'label' => 'Animal', 'young' => 'young',
        'min_age' => ['female' => 12, 'male' => 12], 'max_age' => ['female' => 120, 'male' => 120],
        'gestation_days' => null, 'litter' => null, 'check_size' => false,
    ],
];
