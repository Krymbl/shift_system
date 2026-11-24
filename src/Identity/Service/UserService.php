<?php

declare(strict_types=1);

namespace App\Identity\Service;

use App\Identity\Dto\UserDto;
use App\Identity\Dto\UserSaveDto;
use App\Identity\Dto\UserUpdateEmailDto;
use App\Identity\Dto\UserUpdatePasswordDto;
use App\Identity\Entity\User;
use App\Identity\Enum\Role;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserAlreadyActiveException;
use App\Identity\Exception\UserAlreadyDeactivatedException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Exception\UserNotFoundException;
use App\Identity\Repository\UserRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{

    private const array UNIQUE_EXCEPTIONS = [
        'users_email_unique' => EmailAlreadyTakenException::class,
        'users_phone_unique' => PhoneAlreadyTakenException::class,
    ];

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    //TODO передавать роль при создании в userDto прокинуть роль? Пока мне не нравится, как я это сделал

    public function create(UserSaveDto $dto, Role $role): User
    {
        $user = new User(
            email: $dto->email,
            phone: $dto->phone,
            password: '',
            role: $role,
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            patronymic: $dto->patronymic,
            birthday: $dto->birthday,
            gender: $dto->gender,
        );
        //todo пароль надо на почту отправлять или где-то его сохранять
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->generatePassword()));

        $this->userRepository->save($user, false);

        return $user;
    }

    /**
     * @throws PhoneAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function update(User $user, UserSaveDto $dto): void
    {
       $user->setPhone($dto->phone);
       $user->setLastName($dto->lastName);
       $user->setFirstName($dto->firstName);
       $user->setPatronymic($dto->patronymic);
       $user->setBirthday($dto->birthday);
       $user->setGender($dto->gender);

        try {
            $this->userRepository->save($user);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }
    }

    /**
     * @throws EmailAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    public function updateEmail(User $user, UserUpdateEmailDto $emailDto): void
    {
        $email = $emailDto->email;

        $user->setEmail($email);
        try {
            $this->userRepository->save($user);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }
    }

    public function updatePassword(User $user, UserUpdatePasswordDto $passwordDto): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $passwordDto->newPassword));
        $this->userRepository->save($user);
    }

    /**
     * @throws UserAlreadyDeactivatedException
     * todo проверить, что сотрудник не уволен
     */
    public function deactivate(User $user): void
    {
        if (!$user->isActive()) {
            throw new UserAlreadyDeactivatedException();
        }
        $user->setDeactivatedAt(new \DateTimeImmutable());
        $this->userRepository->save($user);
    }

    /**
     * @throws UserAlreadyActiveException
     * todo проверить, что сотрудник не уволен
     */
    public function activate(User $user): void
    {
        if ($user->isActive()) {
            throw new UserAlreadyActiveException();
        }
        $user->setDeactivatedAt(null);
        $this->userRepository->save($user);
    }

    /**
     * Вызывается только суперадмином в крайних случаях
     * @throws UserHasRelatedDataException
     */
    public function delete(User $user): void
    {
        try {
            $this->userRepository->remove($user);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new UserHasRelatedDataException($user->getId(), $e);
        }
    }

    /**
     * @throws UserNotFoundException
     */
    public function getById(int $id): User
    {
       $user = $this->userRepository->find($id);

       if ($user === null) {
           throw new UserNotFoundException("Пользователь с id $id не найден");
       }

       return $user;
    }

    /**
     * @throws UserNotFoundException
     */
    public function getByEmail(string $email): User
    {
        $email = mb_strtolower(trim($email));

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user === null) {
            throw new UserNotFoundException("Пользователь с email $email не найден");
        }

        return $user;
    }

    /**
     * @return list<UserDto>
     */
    public function getAll(): array
    {
        $users = $this->userRepository->findAll();

        $result = [];
        foreach ($users as $user) {
            $result[] = UserDto::fromUser($user);
        }

        return $result;
    }

    private function generatePassword(int $length = 12): string
    {
        $alphabet = 'abcdefghijkmnpqrstuvwxyzACDEFGHJKLMNPQRSTUVWXYZ123456789';
        $maxIndex = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        return $password;
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    public function mapUniqueViolation(UniqueConstraintViolationException $e): EmailAlreadyTakenException|PhoneAlreadyTakenException
    {
        /**
         * @var class-string<EmailAlreadyTakenException|PhoneAlreadyTakenException> $exceptionClass
         */
        foreach (self::UNIQUE_EXCEPTIONS as $indexName => $exceptionClass) {
            if (str_contains($e->getMessage(), $indexName)) {
                return new $exceptionClass($e);
            }
        }

        throw $e;
    }
}
