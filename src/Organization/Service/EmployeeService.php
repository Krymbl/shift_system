<?php

declare(strict_types=1);

namespace App\Organization\Service;

use App\Customer\Exception\ClientAlreadyExistsException;
use App\Identity\Entity\User;
use App\Identity\Enum\Role;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Service\UserService;
use App\Organization\Dto\EmployeeSaveDto;
use App\Organization\Entity\Employee;
use App\Organization\Exception\EmployeeAlreadyExistsException;
use App\Organization\Exception\EmployeeAlreadyFiredException;
use App\Organization\Exception\EmployeeNotFiredException;
use App\Organization\Exception\EmployeeNotFoundException;
use App\Organization\Repository\EmployeeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class EmployeeService
{
    private const array UNIQUE_EXCEPTIONS = [
        'employee_user_unique' => EmployeeAlreadyExistsException::class,
    ];

    public function __construct(
        private EmployeeRepository $employeeRepository,
        private UserService $userService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws PhoneAlreadyTakenException
     * @throws EmailAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function create(EmployeeSaveDto $dto): Employee
    {
        try {
            return $this->entityManager->wrapInTransaction(function () use ($dto): Employee {
                $userDto = $dto->userSaveDto;
                $user = $this->userService->create($userDto, Role::EMPLOYEE);

                $employee = new Employee(user: $user, hiredAt: new \DateTimeImmutable());
                $this->employeeRepository->save($employee, false);

                return $employee;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw $this->userService->mapUniqueViolation($e);
        }
    }

    /**
     * @throws PhoneAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function update(Employee $employee, EmployeeSaveDto $dto): void
    {
        $this->userService->update($employee->getUser(), $dto->userSaveDto);
    }

    /**
     * @throws UniqueConstraintViolationException
     * @throws EmployeeAlreadyExistsException
     */
    public function link(User $user): Employee
    {
        if ($this->employeeRepository->findOneBy(['user' => $user]) !== null) {
            throw new EmployeeAlreadyExistsException();
        }

        $employee = new Employee(user: $user, hiredAt: new \DateTimeImmutable());
        $user->addRole(Role::EMPLOYEE);
        try {
            $this->employeeRepository->save($employee);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }

        return $employee;
    }

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     * @throws UserHasRelatedDataException
     * todo прокидывать EmployeeHasReleatedDataException
     * todo будто если есть у юзера еще и аккаунта клиента, то надо спрашивать, удалять только сотрудника или полностью учетку с акком клиента
     */
    public function delete(Employee $employee): void
    {
        $this->entityManager->wrapInTransaction(function () use ($employee): void
        {
            $user = $employee->getUser();

            $this->employeeRepository->remove($employee);
            $this->userService->delete($user);
        });
    }

    /**
     * @throws EmployeeAlreadyFiredException
     */
    public function fire(Employee $employee): void
    {
        if ($employee->isFired()) {
            throw new EmployeeAlreadyFiredException();
        }
        $employee->setFiredAt(new \DateTimeImmutable());
        $this->employeeRepository->save($employee);
    }

    /**
     * @throws EmployeeNotFiredException
     */
    public function reverseFire(Employee $employee): void
    {
        if (!$employee->isFired()) {
            throw new EmployeeNotFiredException();
        }
        $employee->setFiredAt(null);
        $this->employeeRepository->save($employee);
    }

    /**
     * @throws EmployeeNotFoundException
     */
    public function getById(int $id, bool $withUser = true): Employee
    {
        $employee = $withUser
            ? $this->employeeRepository->findByIdWithUser($id)
            : $this->employeeRepository->find($id);

        if ($employee === null) {
            throw new EmployeeNotFoundException();
        }

        return $employee;
    }

    /**
     * todo возвращать DTO
     * @return list<Employee>
     */
    public function getAll(bool $withUser = true): array
    {
        return $withUser
            ? $this->employeeRepository->findAllWithUser()
            : $this->employeeRepository->findAll();
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
