<?php

declare(strict_types=1);

namespace App\WorkOrder\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Customer\Entity\Client;
use App\WorkOrder\Repository\WorkObjectRepository;
use Doctrine\ORM\Mapping as ORM;

//work_object_identifier_active_uniq
#[ORM\Entity(repositoryClass: WorkObjectRepository::class)]
#[ORM\Table(options: ['comment' => 'Объект работы - объект клиента'])]
#[ORM\HasLifecycleCallbacks]
class WorkObject
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true,
        options: ['comment' => 'Дополнительный уникальный идентификатор'])]
    private ?string $identifier = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => true, 'default' => '{}', 'comment' => 'Постоянные данные объекта'])]
    private array $attributes = [];

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'ВК Клиент'])]
    private Client $client;

    //work_object_archived_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата архивации'])]
    private ?\DateTimeImmutable $archivedAt = null;

    public function __construct(
        Client $client,
        array $attributes = [],
        ?string $identifier = null,
    ) {
        $this->client = $client;
        $this->attributes = $attributes;
        $this->setIdentifier($identifier);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    //TODO Добавить нормализацию кириллици в латиницу
    public function setIdentifier(?string $identifier): void
    {
        if ($identifier === null) {
            $this->identifier = null;

            return;
        }
        $identifier = mb_strtoupper(preg_replace('/\s+/u', '', $identifier));

        $this->identifier = $identifier === '' ? null : $identifier;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
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
