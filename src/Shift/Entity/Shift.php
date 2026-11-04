<?php

declare(strict_types=1);

namespace App\Shift\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Identity\Entity\User;
use App\Organization\Entity\Employee;
use App\Organization\Entity\EmployeeAssignment;
use App\Shift\Enum\ShiftStatus;
use Doctrine\ORM\Mapping as ORM;

//shift_one_active_per_employee - в миграции
#[ORM\Entity(repositoryClass: ShiftRepository::class)]
#[ORM\Table(options: ['comment' => 'Смены'])]
#[ORM\HasLifecycleCallbacks]
class Shift
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //shift_status_check, shift_status_fields_check
    #[ORM\Column(type: 'string', length: 20, enumType: ShiftStatus::class, options: [
        'comment' => 'Статус: requested (заявка), rejected (отклонена), open (идёт), closed, forced_closed (закрыта админом с незавершёнными задачами)',
    ])]
    private ShiftStatus $status = ShiftStatus::REQUESTED;

    //shift_hourly_rate_check
    #[ORM\Column(type: 'bigint', nullable: true, options: [
        'comment' => 'Снимок ставки за час в копейках',
    ])]
    private ?int $hourlyRate = null;

    #[ORM\ManyToOne(targetEntity: EmployeeAssignment::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Назначение, по которому идёт смена',
    ])]
    private EmployeeAssignment $employeeAssignment;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Сотрудник. Денормализация: дублирует сотрудника из назначения, чтобы работал индекс одной активной смены на человека. Менять нельзя',
    ])]
    private Employee $employee;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Пользователь, подтвердивший или отклонивший заявку',
    ])]
    private ?User $decidedBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Пользователь, закрывший смену',
    ])]
    private ?User $closedBy = null;

    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: [
        'comment' => 'Момент открытия смены',
    ])]
    private ?\DateTimeImmutable $openedAt = null;

    //shift_closed_at_check
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: [
        'comment' => 'Момент закрытия смены. Пусто, пока смена не закрыта',
    ])]
    private ?\DateTimeImmutable $closedAt = null;

    public function __construct(EmployeeAssignment $employeeAssignment)
    {
        $this->employeeAssignment = $employeeAssignment;
        $this->employee = $employeeAssignment->getEmployee();
    }

    public function reject(User $decidedBy): void
    {
        $this->decidedBy = $decidedBy;
        $this->status = ShiftStatus::REJECTED;
    }

    public function open(User $decidedBy): void
    {
        $this->decidedBy = $decidedBy;
        $this->hourlyRate = $this->employeeAssignment->getHourlyRate();
        $this->openedAt = new \DateTimeImmutable();
        $this->status = ShiftStatus::OPEN;
    }

    public function close(User $closedBy, bool $force = false): void
    {
        $this->closedBy = $closedBy;
        $this->closedAt = new \DateTimeImmutable();
        $this->status = $force ? ShiftStatus::FORCED_CLOSED : ShiftStatus::CLOSED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ShiftStatus
    {
        return $this->status;
    }

    public function getHourlyRate(): ?int
    {
        return $this->hourlyRate;
    }

    public function getEmployeeAssignment(): EmployeeAssignment
    {
        return $this->employeeAssignment;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function isOpened(): bool
    {
        return $this->status === ShiftStatus::OPEN;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function isClosed(): bool
    {
        return $this->status === ShiftStatus::CLOSED
            || $this->status === ShiftStatus::FORCED_CLOSED;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function getDecidedBy(): ?User
    {
        return $this->decidedBy;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }
}
