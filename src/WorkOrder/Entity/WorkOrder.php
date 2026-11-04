<?php

declare(strict_types=1);

namespace App\WorkOrder\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Customer\Entity\Client;
use App\Identity\Entity\User;
use App\Organization\Entity\Location;
use App\WorkOrder\Enum\PaymentMethod;
use App\WorkOrder\Enum\WorkOrderStatus;
use Doctrine\ORM\Mapping as ORM;

//work_order_open_by_location
#[ORM\Entity(repositoryClass: WorkOrderRepository::class)]
#[ORM\Table(options: ['comment' => 'Заказ-наряды — визиты клиента'])]
#[ORM\HasLifecycleCallbacks]
class WorkOrder
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //work_order_status_check, work_order_status_fields_check
    #[ORM\Column(type: 'string', length: 20, enumType: WorkOrderStatus::class, options: [
        'comment' => 'Статус: open, closed, cancelled',
    ])]
    private WorkOrderStatus $status = WorkOrderStatus::OPEN;

    //work_order_final_price_check
    #[ORM\Column(type: 'bigint', nullable: true, options: [
        'comment' => 'Снимок итога в копейках, записывается при закрытии и больше не меняется',
    ])]
    private ?int $finalPriceSnapshot = null;

    #[ORM\Column(type: 'json', options: [
        'jsonb' => true,
        'default' => '{}',
        'comment' => 'Данные визита',
    ])]
    private array $visitInfo = [];

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Точка, где принят заказ. Совпадает с точкой его задач',
    ])]
    private Location $location;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Клиент — снимок владельца на момент визита',
    ])]
    private ?Client $client = null;

    // CHECK work_order_client_object_check — в миграции
    #[ORM\ManyToOne(targetEntity: WorkObject::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Объект работы',
    ])]
    private ?WorkObject $workObject = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Пользователь, принявший заказ',
    ])]
    private User $createdBy;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT', options: [
        'comment' => 'ВК Пользователь, закрывший или отменивший заказ',
    ])]
    private ?User $closedBy = null;

    //work_order_closed_at_check
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: [
        'comment' => 'Момент закрытия или отмены',
    ])]
    private ?\DateTimeImmutable $closedAt = null;

    //work_order_payment_pair_check, work_order_paid_at_check
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: [
        'comment' => 'Момент оплаты',
    ])]
    private ?\DateTimeImmutable $paidAt = null;

    //work_order_payment_method_check
    #[ORM\Column(type: 'string', length: 20, nullable: true, enumType: PaymentMethod::class, options: [
        'comment' => 'Способ оплаты: cash, card, transfer. Заполняется вместе с моментом оплаты',
    ])]
    private ?PaymentMethod $paymentMethod = null;

    private function __construct(
        Location $location,
        User $createdBy,
        array $visitInfo = [],
        ?Client $client = null,
        ?WorkObject $workObject = null,
    ) {
        $this->location = $location;
        $this->createdBy = $createdBy;
        $this->visitInfo = $visitInfo;
        $this->client = $client;
        $this->workObject = $workObject;
    }

    public static function anonymous(
        Location $location,
        User $createdBy,
        array $visitInfo = [],
    ): self {
        return new self(
            location: $location,
            createdBy: $createdBy,
            visitInfo: $visitInfo,
        );
    }

    public static function forClient(
        Location $location,
        User $createdBy,
        Client $client,
        array $visitInfo = [],
    ): self {
        return new self(
            location: $location,
            createdBy: $createdBy,
            visitInfo: $visitInfo,
            client: $client,
        );
    }

    public static function forWorkObject(
        Location $location,
        User $createdBy,
        WorkObject $workObject,
        array $visitInfo = [],
    ): self {
        return new self(
            location: $location,
            createdBy: $createdBy,
            visitInfo: $visitInfo,
            client: $workObject->getClient(),
            workObject: $workObject,
        );
    }

    public function assignWorkObject(WorkObject $workObject): void
    {
        $this->workObject = $workObject;
        $this->client = $workObject->getClient();
    }

    public function assignClient(Client $client): void
    {
        $this->client = $client;
        $this->workObject = null;
    }

    public function pay(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
        $this->paidAt = new \DateTimeImmutable();
    }

    public function close(User $closedBy, int $finalPrice): void
    {
        $this->closedBy = $closedBy;
        $this->finalPriceSnapshot = $finalPrice;
        $this->status = WorkOrderStatus::CLOSED;
        $this->closedAt = new \DateTimeImmutable();
    }

    public function cancel(User $closedBy): void
    {
        $this->closedBy = $closedBy;
        $this->status = WorkOrderStatus::CANCELLED;
        $this->closedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): WorkOrderStatus
    {
        return $this->status;
    }

    public function getFinalPriceSnapshot(): ?int
    {
        return $this->finalPriceSnapshot;
    }

    public function getVisitInfo(): array
    {
        return $this->visitInfo;
    }

    public function setVisitInfo(array $visitInfo): void
    {
        $this->visitInfo = $visitInfo;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): void
    {
        $this->client = $client;
    }

    public function getWorkObject(): ?WorkObject
    {
        return $this->workObject;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }
}
