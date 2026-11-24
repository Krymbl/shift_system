<?php

declare(strict_types=1);

namespace App\Organization\Dto;

use App\Organization\Entity\Location;
use Symfony\Component\Validator\Constraints as Assert;

//todo обрабатывать уникальность имени
final class LocationSaveDto
{
    #[Assert\NotBlank(message: 'Имя обязательно')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Название должно содержать не больше {{ limit }} символов',
    )]
    public string $name;

    #[Assert\NotBlank(message: 'Адрес обязателен')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Адрес должен содержать не больше {{ limit }} символов',
    )]
    public string $address;

    #[Assert\Regex(pattern: '/^\+\d{11,15}$/',
        message: 'Проверьте номер телефона, например +7 900 255-45-36 или 8 900 255-45-36')]
    public ?string $phone = null;

    public static function fromLocation(Location $location): self
    {
        $dto = new self();
        $dto->name = $location->getName();
        $dto->address = $location->getAddress();
        $dto->phone = $location->getPhone();

        return $dto;
    }
}
