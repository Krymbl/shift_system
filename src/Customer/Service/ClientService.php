<?php
declare(strict_types=1);

namespace App\Customer\Service;

use App\Customer\Dto\ClientSaveDto;
use App\Customer\Entity\Client;
use App\Customer\Exception\ClientNotFoundException;
use App\Customer\Repository\ClientRepository;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ClientService
{

    public function __construct(
        private ClientRepository $clientRepository,
        private UserService $userService,
        private EntityManagerInterface $entityManager,
    ) {

    }
    /**
     * @throws PhoneAlreadyTakenException
     * @throws EmailAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function create(ClientSaveDto $dto): Client
    {
        return $this->entityManager->wrapInTransaction(function () use ($dto): Client
        {
            $userDto = $dto->userSaveDto;
            $user = $this->userService->create($userDto);

            $client = new Client(user: $user, note: $dto->note);
            $this->clientRepository->save($client, false);

            return $client;
        });
    }

    public function update(Client $client, ClientSaveDto $dto): void
    {
        $client->setNote($dto->note);
        $this->clientRepository->save($client);
    }

    /**
     * @throws UserHasRelatedDataException
     */
    public function delete(Client $client): void
    {
        $this->entityManager->wrapInTransaction(function () use ($client): void
        {
            $this->userService->delete($client->getUser());
            $this->clientRepository->remove($client);
        });
    }

    /**
     * @throws ClientNotFoundException
     */
    public function getById(int $id): Client
    {
        $client = $this->clientRepository->find($id);

        if ($client === null) {
            throw new ClientNotFoundException($id);
        }

        return $client;
    }
}
