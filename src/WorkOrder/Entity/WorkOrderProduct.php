<?php

declare(strict_types=1);

namespace App\WorkOrder\Entity;

use App\Catalog\Entity\Product;
use App\Common\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkOrderProductRepository::class)]
#[ORM\Table(options: ['comment' => 'Строки товаров заказ-наряда'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(
    name: 'work_order_product_unique',
    columns: ['work_order_id', 'product_id'],
)]
class WorkOrderProduct
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //work_order_product_quantity_check
    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, options: [
        'comment' => 'Количество, до трёх знаков после запятой'])]
    private string $quantity;

    //work_order_product_unit_price_check
    #[ORM\Column(type: 'bigint', options: ['comment' => 'Снимок цены за единицу в копейках на момент добавления'])]
    private int $unitPrice;

    #[ORM\ManyToOne(targetEntity: WorkOrder::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: [
        'comment' => 'Заказ-наряд. Менять и удалять строки можно только пока он открыт',
    ])]
    private WorkOrder $workOrder;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT', options: ['comment' => 'Товар из каталога'])]
    private Product $product;

    public function __construct(
        string $quantity,
        int $unitPrice,
        WorkOrder $workOrder,
        Product $product,
    ) {
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->workOrder = $workOrder;
        $this->product = $product;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getWorkOrder(): WorkOrder
    {
        return $this->workOrder;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }
}
