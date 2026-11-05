<?php

declare(strict_types=1);

namespace App\Organization\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Organization\Repository\PositionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\Table(options: ['comment' => 'Справочник должностей организации'])]
#[ORM\HasLifecycleCallbacks]
class Position
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //position_name_lower_uniq
    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Название, имя уникально без учёта регистра'])]
    private string $name;

    //position_archived_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата архивации'])]
    private ?\DateTimeImmutable $archivedAt = null;

    public function __construct(string $name)
    {
        $this->name = $name;
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

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeImmutable $archivedAt): void
    {
        $this->archivedAt = $archivedAt;
    }
}
