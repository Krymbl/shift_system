<?php

declare(strict_types=1);

namespace App\Organization\Repository;

use App\Organization\Entity\EmployeeAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeAssignment>
 */
final class EmployeeAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeAssignment::class);
    }

    public function save(EmployeeAssignment $employeeAssignment, bool $flush = true): void
    {
        $this->getEntityManager()->persist($employeeAssignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmployeeAssignment $employeeAssignment, bool $flush = true): void
    {
        $this->getEntityManager()->remove($employeeAssignment);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
