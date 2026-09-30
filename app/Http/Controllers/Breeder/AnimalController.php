<?php

namespace App\Http\Controllers\Breeder;

use App\Http\Controllers\Seller\AnimalController as SellerAnimalController;

/** A breeder manages their studs and dams with the same tools a seller has, but every listing is "breeding". */
class AnimalController extends SellerAnimalController
{
    protected string $area = 'breeder';

    protected bool $breedingOnly = true;
}
