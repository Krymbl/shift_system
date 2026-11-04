<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use App\Catalog\Enum\ProductUnit;
use App\Common\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(options: ['comment' => 'Продукт/товар'])]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true, nullable: true,
        options: ['comment' => 'Артикул (уникальный идентификатор)'])]
    private ?string $article = null;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Название'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Описание'])]
    private ?string $description = null;

    //product_price_check
    #[ORM\Column(type: 'bigint', options: ['comment' => 'Цена копейки'])]
    private int $price;

    //product_unit_check
    #[ORM\Column(type: 'string', length: 10, enumType: ProductUnit::class,
        options: ['comment' => 'Единица измерения (pcs, l, m, kg)'])]
    private ProductUnit $unit;

    //product_archived_at_check
    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'Дата архивации'])]
    private ?\DateTimeImmutable $archivedAt = null;


    public function __construct(
        string $name,
        int $price,
        ProductUnit $unit,
        ?string $description = null,
        ?string $article = null,
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->unit = $unit;
        $this->description = $description;
        $this->setArticle($article);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?string
    {
        return $this->article;
    }

    public function setArticle(?string $article): void
    {
        if ($article === null) {
            $this->article = null;

            return;
        }
        $article = mb_strtoupper(preg_replace('/\s+/u', '', $article));

        $this->article = $article === '' ? null : $article;
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

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getUnit(): ProductUnit
    {
        return $this->unit;
    }

    public function setUnit(ProductUnit $unit): void
    {
        $this->unit = $unit;
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
