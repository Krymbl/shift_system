<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use App\Common\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceChecklistItemRepository::class)]
#[ORM\Table(options: ['comment' => 'Пункты шаблонного чек-листа услуги'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'service_checklist_item_position_by_service_type_idx', columns: ['service_type_id', 'position'])]
class ServiceChecklistItem
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['comment' => 'Позиция в списке'])]
    private int $position;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Текст'])]
    private string $text;

    #[ORM\ManyToOne(targetEntity: ServiceType::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'ВК Услуга'])]
    private ServiceType $serviceType;

    public function __construct(
        int $position,
        string $text,
        ServiceType $serviceType
    ) {
        $this->position = $position;
        $this->text = $text;
        $this->serviceType = $serviceType;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getServiceType(): ServiceType
    {
        return $this->serviceType;
    }
}
