<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\TaskStatusChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskStatusChange>
 */
final class TaskStatusChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskStatusChange::class);
    }

    public function save(TaskStatusChange $taskStatusChange, bool $flush = true): void
    {
        $this->getEntityManager()->persist($taskStatusChange);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TaskStatusChange $taskStatusChange, bool $flush = true): void
    {
        $this->getEntityManager()->remove($taskStatusChange);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
