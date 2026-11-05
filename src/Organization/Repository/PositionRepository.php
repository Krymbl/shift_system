<?php

declare(strict_types=1);

namespace App\Organization\Repository;

use App\Organization\Entity\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Position>
 */
final class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function save(Position $position, bool $flush = true): void
    {
        $this->getEntityManager()->persist($position);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Position $position, bool $flush = true): void
    {
        $this->getEntityManager()->remove($position);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
