<?php

declare(strict_types=1);

namespace App\Organization\Dto;

class LocationSaveDto
{
    private string $name;
    private string $address;
    private ?string $phone = null;

}
