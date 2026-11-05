<?php

declare(strict_types=1);

namespace App\WorkOrder\Repository;

use App\WorkOrder\Entity\WorkObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkObject>
 */
final class WorkObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkObject::class);
    }

    public function save(WorkObject $workObject, bool $flush = true): void
    {
        $this->getEntityManager()->persist($workObject);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WorkObject $workObject, bool $flush = true): void
    {
        $this->getEntityManager()->remove($workObject);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
