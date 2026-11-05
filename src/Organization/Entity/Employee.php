<?php

declare(strict_types=1);

namespace App\Organization\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Identity\Entity\User;
use App\Organization\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(options: ['comment' => 'Сотрудники'])]
#[ORM\HasLifecycleCallbacks]
class Employee
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', options: ['default' => 0, 'comment' => 'Баланс в копейках. Кэш журнала начислений'])]
    private int $balance = 0;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Пользователь. Учётная запись сотрудника'])]
    private User $user;

    #[ORM\Column(type: 'date_immutable', options: ['comment' => 'Дата найма'])]
    private \DateTimeImmutable $hiredAt;

    //employee_fired_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата увольнения'])]
    private ?\DateTimeImmutable $firedAt = null;

    public function __construct(
        User $user,
        \DateTimeImmutable $hiredAt
    ) {
        $this->user = $user;
        $this->hiredAt = $hiredAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Копейки, не рубли!
     */
    public function applyLedgerAmount(int $amount): void
    {
        $this->balance += $amount;
    }

    public function getHiredAt(): \DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function setHiredAt(\DateTimeImmutable $hiredAt): void
    {
        $this->hiredAt = $hiredAt;
    }

    public function isFired(): bool
    {
        return $this->firedAt !== null;
    }

    public function getFiredAt(): ?\DateTimeImmutable
    {
        return $this->firedAt;
    }

    public function setFiredAt(?\DateTimeImmutable $firedAt): void
    {
        $this->firedAt = $firedAt;
    }
}
