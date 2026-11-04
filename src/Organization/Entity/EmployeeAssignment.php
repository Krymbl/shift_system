<?php

declare(strict_types=1);

namespace App\Organization\Entity;

use App\Common\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

//employee_assignment_active_uniq — частичный уникальный индекс, в миграции
#[ORM\Entity(repositoryClass: EmployeeAssignmentRepository::class)]
#[ORM\Table(options: ['comment' => 'Назначения сотрудника (должности на точках)'])]
#[ORM\HasLifecycleCallbacks]
class EmployeeAssignment
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //employee_assignment_hourly_rate_check
    #[ORM\Column(type: 'bigint', options: ['comment' => 'Ставка за час в копейках'])]
    private int $hourlyRate;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'ВК Сотрудник'])]
    private Employee $employee;

    #[ORM\ManyToOne(targetEntity: Position::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'ВК Должность'])]
    private Position $position;

    #[ORM\ManyToOne(targetEntity: Department::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'ВК Отдел'])]
    private Department $department;

    //employee_assignment_started_at_check
    #[ORM\Column(type: 'date_immutable', options: ['comment' => 'Дата старта'])]
    private \DateTimeImmutable $startedAt;

    //employee_assignment_ended_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата окончания'])]
    private ?\DateTimeImmutable $endedAt = null;

    public function __construct(
        int $hourlyRate,
        Employee $employee,
        Position $position,
        Department $department,
        \DateTimeImmutable $startedAt,
    ) {
        $this->hourlyRate = $hourlyRate;
        $this->employee = $employee;
        $this->position = $position;
        $this->department = $department;
        $this->startedAt = $startedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHourlyRate(): int
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(int $hourlyRate): void
    {
        $this->hourlyRate = $hourlyRate;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): void
    {
        $this->endedAt = $endedAt;
    }
}
