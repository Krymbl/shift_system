<?php

declare(strict_types=1);

namespace App\Organization\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Organization\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(options: ['comment' => 'Точки (филиалы)'])]
#[ORM\HasLifecycleCallbacks]
class Location
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //location_name_lower_uniq
    #[ORM\Column(type: 'string', length: 255,
        options: ['comment' => 'Название, уникально без учета регистра, имя закрытой точки не переиспользуется'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Адрес'])]
    private string $address;

    #[ORM\Column(type: 'string', length: 30, nullable: true, options: ['comment' => 'Рабочий телефон'])]
    private ?string $phone = null;

    //location_closed_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата закрытия'])]
    private ?\DateTimeImmutable $closedAt = null;

    public function __construct(
        string $name,
        string $address,
        ?string $phone = null,
    ) {
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    //TODO нормализатор телефона
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function isClosed(): bool
    {
        return $this->closedAt !== null;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): void
    {
        $this->closedAt = $closedAt;
    }
}
