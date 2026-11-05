<?php

declare(strict_types=1);

namespace App\Organization\Repository;

use App\Organization\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Department>
 */
final class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function save(Department $department, bool $flush = true): void
    {
        $this->getEntityManager()->persist($department);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Department $department, bool $flush = true): void
    {
        $this->getEntityManager()->remove($department);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
