<?php

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\ServiceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceType>
 */
final class ServiceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceType::class);
    }

    public function save(ServiceType $serviceType, bool $flush = true): void
    {
        $this->getEntityManager()->persist($serviceType);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceType $serviceType, bool $flush = true): void
    {
        $this->getEntityManager()->remove($serviceType);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
