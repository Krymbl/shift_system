<?php

declare(strict_types=1);

namespace App\Organization\Repository;

use App\Organization\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
final class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     * @throws UniqueConstraintViolationException
     */
    public function save(Employee $employee, bool $flush = true): void
    {
        $this->getEntityManager()->persist($employee);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employee $employee, bool $flush = true): void
    {
        $this->getEntityManager()->remove($employee);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * С жадной подгрузкой юзера
     */
    public function findByIdWithUser(int $id): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.user', 'u')
            ->addSelect('u')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * С жадной подгрузкой юзера
     * @return list<Employee>
     */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
