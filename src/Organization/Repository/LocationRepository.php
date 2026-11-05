<?php

declare(strict_types=1);

namespace App\Organization\Repository;

use App\Organization\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
final class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function save(Location $location, bool $flush = true): void
    {
        $this->getEntityManager()->persist($location);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Location $location, bool $flush = true): void
    {
        $this->getEntityManager()->remove($location);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
