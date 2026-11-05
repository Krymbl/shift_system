<?php

declare(strict_types=1);

namespace App\WorkOrder\Repository;

use App\WorkOrder\Entity\WorkOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkOrder>
 */
final class WorkOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkOrder::class);
    }

    public function save(WorkOrder $workOrder, bool $flush = true): void
    {
        $this->getEntityManager()->persist($workOrder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WorkOrder $workOrder, bool $flush = true): void
    {
        $this->getEntityManager()->remove($workOrder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
