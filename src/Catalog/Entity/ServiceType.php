<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Organization\Entity\Position;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceTypeRepository::class)]
#[ORM\Table(options: ['comment' => 'Услуга'])]
#[ORM\HasLifecycleCallbacks]
class ServiceType
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    // UNIQUE INDEX по lower(name), в миграции
    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Название, уникально без учёта регистра'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Описание'])]
    private ?string $description = null;

    //check > 0
    #[ORM\Column(type: 'bigint', nullable: true, options: ['comment' => 'Цена для клиента в копейках'])]
    private ?int $clientPrice = null;

    //check >= 0
    #[ORM\Column(type: 'bigint', options: ['comment' => 'Оплата сотруднику в копейках'])]
    private int $employeePay;

    //check > 0
    #[ORM\Column(type: 'integer', options: ['comment' => 'Время выполнения в минутах'])]
    private int $durationMinutes;

    //CHECK (is_internal = true  AND client_price IS NULL)
    // OR (is_internal = false AND client_price IS NOT NULL)
    #[ORM\Column(type: 'boolean', options: ['default' => false, 'comment' => "Флаг 'Внутренняя услуга'"])]
    private bool $isInternal;

    #[ORM\ManyToOne(targetEntity: Position::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: ['comment' => 'ВК Рекомендуемая должность'])]
    private ?Position $requiredPosition = null;

    //CHECK (archived_at <= CURRENT_DATE)
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата архивации'])]
    private ?\DateTimeImmutable $archivedAt = null;


    public static function internal(
        string $name,
        int $durationMinutes,
        int $employeePay,
        ?string $description = null,
        ?Position $requiredPosition = null,
    ): self {
        $service = new self(
            name: $name,
            durationMinutes: $durationMinutes,
            employeePay: $employeePay,
            description: $description,
            requiredPosition: $requiredPosition
        );

        $service->makeInternal();

        return $service;
    }

    public static function billable(
        string $name,
        int $durationMinutes,
        int $employeePay,
        int $clientPrice,
        ?string $description = null,
        ?Position $requiredPosition = null,
    ): self {
        $service = new self(
            name: $name,
            durationMinutes: $durationMinutes,
            employeePay: $employeePay,
            description: $description,
            requiredPosition: $requiredPosition
        );

        $service->makeBillable($clientPrice);

        return $service;
    }

    private function __construct(
        string $name,
        int $durationMinutes,
        int $employeePay,
        ?string $description = null,
        ?Position $requiredPosition = null,
    ) {
        $this->name = $name;
        $this->durationMinutes = $durationMinutes;
        $this->employeePay = $employeePay;
        $this->description = $description;
        $this->requiredPosition = $requiredPosition;
    }

    public function makeInternal(): void
    {
        $this->isInternal = true;
        $this->clientPrice = null;
    }

    public function makeBillable(int $clientPrice): void
    {
        $this->isInternal = false;
        $this->clientPrice = $clientPrice;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getClientPrice(): ?int
    {
        return $this->clientPrice;
    }

    public function getEmployeePay(): int
    {
        return $this->employeePay;
    }

    public function setEmployeePay(int $employeePay): void
    {
        $this->employeePay = $employeePay;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): void
    {
        $this->durationMinutes = $durationMinutes;
    }

    public function getRequiredPosition(): ?Position
    {
        return $this->requiredPosition;
    }

    public function setRequiredPosition(?Position $requiredPosition): void
    {
        $this->requiredPosition = $requiredPosition;
    }

    public function isInternal(): bool
    {
        return $this->isInternal;
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
