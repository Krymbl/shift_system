<?php

declare(strict_types=1);

namespace App\Customer\Repository;

use App\Customer\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
final class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     * @throws UniqueConstraintViolationException
     */
    public function save(Client $client, bool $flush = true): void
    {
        $this->getEntityManager()->persist($client);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Client $client, bool $flush = true): void
    {
        $this->getEntityManager()->remove($client);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * С жадной подгрузкой юзера
     */
    public function findByIdWithUser(int $id): ?Client
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->addSelect('u')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * С жадной подгрузкой юзера
     * @return list<Client>
     */
    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
