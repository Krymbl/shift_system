<?php

declare(strict_types=1);

namespace App\Organization\Service;

use App\Organization\Repository\LocationRepository;

final class LocationService
{
    public function __construct(
        private LocationRepository $locationRepository
    ) {
    }

    public function create()
    {

    }

}
