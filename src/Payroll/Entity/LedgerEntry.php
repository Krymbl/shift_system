<?php

declare(strict_types=1);

namespace App\Payroll\Entity;

use App\Identity\Entity\User;
use App\Organization\Entity\Employee;
use App\Payroll\Enum\LedgerEntryType;
use App\Payroll\Repository\LedgerEntryRepository;
use App\Shift\Entity\Shift;
use App\Task\Entity\Task;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LedgerEntryRepository::class)]
#[ORM\Table(options: ['comment' => 'Журнал начислений сотрудникам. Неизменяемый: ошибки правятся корректировками'])]
#[ORM\Index(name: 'ledger_entry_employee_history', columns: ['employee_id', 'created_at'])]
class LedgerEntry
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //ledger_entry_amount_check
    #[ORM\Column(type: 'bigint', options: ['comment' => 'Сумма в копейках, положительная'])]
    private int $amount;

    //ledger_entry_type_check, ledger_entry_source_check
    #[ORM\Column(type: 'string', length: 20, enumType: LedgerEntryType::class,
        options: ['comment' => 'Тип: shift (почасовая), task (за задачу), bonus, penalty, payout (выплата), correction_plus, correction_minus'])]
    private LedgerEntryType $type;

    //ledger_entry_comment_check
    #[ORM\Column(type: 'text', nullable: true,
        options: ['comment' => 'Комментарий к записи. Обязательно для всех типов, кроме shift и task'])]
    private ?string $comment = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Сотрудник, которому идёт начисление'])]
    private Employee $employee;

    #[ORM\OneToOne(targetEntity: Shift::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Смена — источник почасовой оплаты. Одна смена даёт не более одной записи'])]
    private ?Shift $shift = null;

    #[ORM\OneToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Задача — источник оплаты за работу. Одна задача даёт не более одной записи'])]
    private ?Task $task = null;

    //ledger_entry_correction_check
    #[ORM\ManyToOne(targetEntity: LedgerEntry::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Исправляемая запись. Заполняется только у корректировок'])]
    private ?LedgerEntry $correctedEntry = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Пользователь, создавший запись. Пусто — запись создала система'])]
    private ?User $createdBy = null;

    #[ORM\Column(type: 'datetimetz_immutable', options: ['comment' => 'Дата создания'])]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        int $amount,
        LedgerEntryType $type,
        Employee $employee,
        ?User $createdBy = null,
        ?string $comment = null,
        ?Shift $shift = null,
        ?Task $task = null,
        ?LedgerEntry $correctedEntry = null
    ) {
        $this->amount = $amount;
        $this->type = $type;
        $this->employee = $employee;
        $this->createdBy = $createdBy;
        $this->comment = $comment;
        $this->shift = $shift;
        $this->task = $task;
        $this->correctedEntry = $correctedEntry;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function byShift(
        Shift $shift,
        int $amount,
        Employee $employee,
    ): self {
        return new self(
            amount: $amount,
            type: LedgerEntryType::SHIFT,
            employee: $employee,
            shift: $shift,
        );
    }

    public static function byTask(
        Task $task,
        int $amount,
        Employee $employee,
    ): self {
        return new self(
            amount: $amount,
            type: LedgerEntryType::TASK,
            employee: $employee,
            task: $task,
        );
    }

    public static function byCorrection(
        LedgerEntry $correctedEntry,
        LedgerEntryType $type,
        int $amount,
        string $comment,
        ?User $createdBy = null,
    ): self {
        return new self(
            amount: $amount,
            type: $type,
            employee: $correctedEntry->getEmployee(),
            createdBy: $createdBy,
            comment: $comment,
            correctedEntry: $correctedEntry,
        );
    }

    public static function manual(
        LedgerEntryType $type,
        Employee $employee,
        int $amount,
        string $comment,
        ?User $createdBy = null,
    ): self {
        return new self(
            amount: $amount,
            type: $type,
            employee: $employee,
            createdBy: $createdBy,
            comment: $comment,
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getType(): LedgerEntryType
    {
        return $this->type;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function getCorrectedEntry(): ?LedgerEntry
    {
        return $this->correctedEntry;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
