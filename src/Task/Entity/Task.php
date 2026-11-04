<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Catalog\Entity\ServiceType;
use App\Common\Traits\TimestampableTrait;
use App\Identity\Entity\User;
use App\Organization\Entity\Location;
use App\Organization\Entity\Position;
use App\Shift\Entity\Shift;
use App\Task\Enum\TaskStatus;
use App\WorkOrder\Entity\WorkOrder;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(options: [
    'comment' => 'Задачи — единицы работы. Часть заказ-наряда или внутренняя',
])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(
    name: 'task_active_by_location',
    columns: ['location_id', 'status'],
    options: ['where' => "status IN ('new', 'assigned', 'in_progress', 'submitted')"],
)]
class Task
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer', options: ['comment' => 'Первичный ключ'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, options: [
        'comment' => 'Название. По умолчанию копируется из услуги',
    ])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true, options: [
        'comment' => 'Описание от админа',
    ])]
    private ?string $description = null;

    //task_status_check, task_status_shift_check
    #[ORM\Column(type: 'string', length: 20, enumType: TaskStatus::class, options: [
        'comment' => 'Статус: new (без смены), assigned, in_progress, submitted (сдана на приёмку), accepted, cancelled',
    ])]
    private TaskStatus $status = TaskStatus::NEW;

    //task_employee_pay_check
    #[ORM\Column(type: 'bigint', options: [
        'comment' => 'Снимок оплаты исполнителю в копейках, взят из услуги при создании. Не меняется: по нему начисляются деньги',
    ])]
    private int $employeePaySnapshot;

    //task_client_price_check
    #[ORM\Column(type: 'bigint', nullable: true, options: [
        'comment' => 'Снимок цены для клиента в копейках. Пусто у внутренних работ. Не меняется: входит в итог заказ-наряда',
    ])]
    private ?int $clientPriceSnapshot = null;

    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: [
        'comment' => 'Срок выполнения - дедлайн',
    ])]
    private ?\DateTimeImmutable $deadlineAt = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'Точка. Совпадает с точкой смены и заказ-наряда',
    ])]
    private Location $location;

    #[ORM\ManyToOne(targetEntity: WorkOrder::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'Заказ-наряд. Пусто у внутренней задачи без заказа',
    ])]
    private ?WorkOrder $workOrder = null;

    #[ORM\ManyToOne(targetEntity: Shift::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'Смена исполнителя',
    ])]
    private ?Shift $shift = null;

    #[ORM\ManyToOne(targetEntity: ServiceType::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'Услуга-шаблон, из которой взяты снимки цен и чек-лист',
    ])]
    private ?ServiceType $serviceType = null;

    #[ORM\ManyToOne(targetEntity: Position::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'Рекомендуемая должность, по умолчанию из услуги',
    ])]
    private ?Position $requiredPosition = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'Кто создал задачу',
    ])]
    private User $createdBy;

    public function __construct(
        string $name,
        int $employeePaySnapshot,
        Location $location,
        User $createdBy,
        ?string $description = null,
        ?int $clientPriceSnapshot = null,
        ?\DateTimeImmutable $deadlineAt = null,
        ?WorkOrder $workOrder = null,
        ?ServiceType $serviceType = null,
        ?Position $requiredPosition = null,
    ) {
        $this->name = $name;
        $this->employeePaySnapshot = $employeePaySnapshot;
        $this->location = $location;
        $this->createdBy = $createdBy;
        $this->description = $description;
        $this->clientPriceSnapshot = $clientPriceSnapshot;
        $this->deadlineAt = $deadlineAt;
        $this->workOrder = $workOrder;
        $this->serviceType = $serviceType;
        $this->requiredPosition = $requiredPosition;
    }

    public function assign(Shift $shift): void
    {
        $this->shift = $shift;
        $this->status = TaskStatus::ASSIGNED;
    }

    public function start(): void
    {
        $this->status = TaskStatus::IN_PROGRESS;
    }

    public function submit(): void
    {
        $this->status = TaskStatus::SUBMITTED;
    }

    public function accept(): void
    {
        $this->status = TaskStatus::ACCEPTED;
    }

    public function returnToWork(): void
    {
        $this->status = TaskStatus::IN_PROGRESS;
    }

    public function cancel(): void
    {
        $this->status = TaskStatus::CANCELLED;
    }

    /**
     * Освобождение из смены при её закрытии: задача возвращается в общий пул.
     */
    public function release(): void
    {
        $this->releaseFromShift();
    }

    public function reopen(): void
    {
        $this->releaseFromShift();
    }

    private function releaseFromShift(): void
    {
        $this->shift = null;
        $this->status = TaskStatus::NEW;
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

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getEmployeePaySnapshot(): int
    {
        return $this->employeePaySnapshot;
    }

    public function getClientPriceSnapshot(): ?int
    {
        return $this->clientPriceSnapshot;
    }

    public function getDeadlineAt(): ?\DateTimeImmutable
    {
        return $this->deadlineAt;
    }

    public function setDeadlineAt(?\DateTimeImmutable $deadlineAt): void
    {
        $this->deadlineAt = $deadlineAt;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getWorkOrder(): ?WorkOrder
    {
        return $this->workOrder;
    }

    public function setWorkOrder(?WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function getServiceType(): ?ServiceType
    {
        return $this->serviceType;
    }

    public function getRequiredPosition(): ?Position
    {
        return $this->requiredPosition;
    }

    public function setRequiredPosition(?Position $requiredPosition): void
    {
        $this->requiredPosition = $requiredPosition;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }
}
