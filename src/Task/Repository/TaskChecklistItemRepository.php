<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\TaskChecklistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskChecklistItem>
 */
final class TaskChecklistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskChecklistItem::class);
    }

    public function save(TaskChecklistItem $taskChecklistItem, bool $flush = true): void
    {
        $this->getEntityManager()->persist($taskChecklistItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TaskChecklistItem $taskChecklistItem, bool $flush = true): void
    {
        $this->getEntityManager()->remove($taskChecklistItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
