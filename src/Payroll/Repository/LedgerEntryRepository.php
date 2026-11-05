<?php

declare(strict_types=1);

namespace App\Payroll\Repository;

use App\Payroll\Entity\LedgerEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LedgerEntry>
 */
final class LedgerEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LedgerEntry::class);
    }

    public function save(LedgerEntry $ledgerEntry, bool $flush = true): void
    {
        $this->getEntityManager()->persist($ledgerEntry);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LedgerEntry $ledgerEntry, bool $flush = true): void
    {
        $this->getEntityManager()->remove($ledgerEntry);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
