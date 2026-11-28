<?php

declare(strict_types=1);

namespace App\Customer\Service;

use App\Customer\Dto\ClientLinkDto;
use App\Customer\Dto\ClientSaveDto;
use App\Customer\Entity\Client;
use App\Customer\Exception\ClientAlreadyExistsException;
use App\Customer\Exception\ClientHasRelatedDataException;
use App\Customer\Exception\ClientNotFoundException;
use App\Customer\Repository\ClientRepository;
use App\Identity\Entity\User;
use App\Identity\Enum\Role;
use App\Identity\Exception\UserDeactivatedException;
use App\Identity\Exception\UserEmailAlreadyTakenException;
use App\Identity\Exception\UserPhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Service\UserService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ClientService
{
    private const array UNIQUE_EXCEPTIONS = [
        'client_user_unique' => ClientAlreadyExistsException::class,
    ];

    public function __construct(
        private ClientRepository $clientRepository,
        private UserService $userService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws UserPhoneAlreadyTakenException
     * @throws UserEmailAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function create(ClientSaveDto $dto): Client
    {
        try {
            return $this->entityManager->wrapInTransaction(function () use ($dto): Client {
                $userDto = $dto->userSaveDto;
                $user = $this->userService->create($userDto, Role::CLIENT);

                $client = new Client(user: $user, note: $dto->note);
                $this->clientRepository->save($client, false);

                return $client;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw $this->userService->mapUniqueViolation($e);
        }
    }

    /**
     * @throws UserPhoneAlreadyTakenException
     * @throws UniqueConstraintViolationException
     * @throws UserDeactivatedException
     */
    public function update(Client $client, ClientSaveDto $dto): void
    {
        $client->setNote($dto->note);
        $this->userService->update($client->getUser(), $dto->userSaveDto);
    }

    /**
     * @throws ClientAlreadyExistsException
     * @throws UniqueConstraintViolationException
     * @throws UserDeactivatedException
     */
    public function link(ClientLinkDto $dto, User $user): Client
    {
        if ($this->clientRepository->findOneBy(['user' => $user]) !== null) {
            throw new ClientAlreadyExistsException();
        }

        $this->userService->ensureActive($user);

        $client = new Client(user: $user, note: $dto->note);
        $user->addRole(Role::CLIENT);
        try {
            $this->clientRepository->save($client);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }

        return $client;
    }

    /**
     * @throws ClientHasRelatedDataException
     * todo будто если есть у юзера еще и аккаунта сотрудника, то надо спрашивать, удалять только клиента или полностью учетку с акком сотрудника
     */
    public function delete(Client $client): void
    {
        try {
            $this->entityManager->wrapInTransaction(function () use ($client): void
            {
                $client->getUser()->removeRole(Role::CLIENT);
                $this->clientRepository->remove($client);
            });
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new ClientHasRelatedDataException();
        }

    }

    /**
     * @throws ClientNotFoundException
     */
    public function getById(int $id, bool $withUser = true): Client
    {
        $client = $withUser
            ? $this->clientRepository->findByIdWithUser($id)
            : $this->clientRepository->find($id);

        if ($client === null) {
            throw new ClientNotFoundException($id);
        }

        return $client;
    }

    /**
     * todo возвращать DTO
     * @return list<Client>
     */
    public function getAll(bool $withUser = true): array
    {
        return $withUser
            ? $this->clientRepository->findAllWithUser()
            : $this->clientRepository->findAll();
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    public function mapUniqueViolation(UniqueConstraintViolationException $e): ClientAlreadyExistsException
    {
        /**
         * @var class-string<ClientAlreadyExistsException> $exceptionClass
         */
        foreach (self::UNIQUE_EXCEPTIONS as $indexName => $exceptionClass) {
            if (str_contains($e->getMessage(), $indexName)) {
                return new $exceptionClass($e);
            }
        }

        throw $e;
    }
}
