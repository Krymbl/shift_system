<?php

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\ServiceChecklistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceChecklistItem>
 */
final class ServiceChecklistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceChecklistItem::class);
    }

    public function save(ServiceChecklistItem $serviceChecklistItem, bool $flush = true): void
    {
        $this->getEntityManager()->persist($serviceChecklistItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceChecklistItem $serviceChecklistItem, bool $flush = true): void
    {
        $this->getEntityManager()->remove($serviceChecklistItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
