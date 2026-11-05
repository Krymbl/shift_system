<?php

declare(strict_types=1);

namespace App\Organization\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Organization\Repository\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(options: ['comment' => 'Отделы точек'])]
#[ORM\HasLifecycleCallbacks]
class Department
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //department_location_name_active
    #[ORM\Column(type: 'string', length: 255,
        options: ['comment' => 'Название, уникально без учёта регистра среди незакрытых отделов этой точки'])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'ВК Точка'])]
    private Location $location;

    //department_closed_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата закрытия'])]
    private ?\DateTimeImmutable $closedAt = null;

    public function __construct(
        string $name,
        Location $location,
    ) {
        $this->name = $name;
        $this->location = $location;
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

    public function getLocation(): Location
    {
        return $this->location;
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
