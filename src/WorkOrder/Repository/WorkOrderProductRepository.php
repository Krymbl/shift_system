<?php

declare(strict_types=1);

namespace App\WorkOrder\Repository;

use App\WorkOrder\Entity\WorkOrderProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkOrderProduct>
 */
final class WorkOrderProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkOrderProduct::class);
    }

    public function save(WorkOrderProduct $workOrderProduct, bool $flush = true): void
    {
        $this->getEntityManager()->persist($workOrderProduct);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WorkOrderProduct $workOrderProduct, bool $flush = true): void
    {
        $this->getEntityManager()->remove($workOrderProduct);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
