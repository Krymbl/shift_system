<?php

declare(strict_types=1);

namespace App\Shift\Repository;

use App\Shift\Entity\Shift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shift>
 */
final class ShiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shift::class);
    }

    public function save(Shift $shift, bool $flush = true): void
    {
        $this->getEntityManager()->persist($shift);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Shift $shift, bool $flush = true): void
    {
        $this->getEntityManager()->remove($shift);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
