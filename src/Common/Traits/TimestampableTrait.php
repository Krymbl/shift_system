<?php

declare(strict_types=1);

namespace App\Common\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Добавляет в сущность поля created_at и updated_at.
 * Обязательно надо повесить на класс #[ORM\HasLifecycleCallbacks]
 */
trait TimestampableTrait
{

    #[ORM\Column(type: 'datetimetz_immutable', options: ['comment' => 'Дата создания'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetimetz_immutable', options: ['comment' => 'Дата обновления'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    public function initTimestamps(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
