<?php

declare(strict_types=1);

namespace App\Identity\Service;

use App\Identity\Dto\UserSaveDto;
use App\Identity\Dto\UserUpdateEmailDto;
use App\Identity\Dto\UserUpdatePasswordDto;
use App\Identity\Entity\User;
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

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    private const array UNIQUE_EXCEPTIONS = [
        'users_email_unique' => EmailAlreadyTakenException::class,
        'users_phone_unique' => PhoneAlreadyTakenException::class,
    ];

    /**
     * @throws EmailAlreadyTakenException
     * @throws PhoneAlreadyTakenException
     * @throws UniqueConstraintViolationException
     */
    //TODO передавать роль при создании
    public function create(UserSaveDto $dto): User
    {
        $user = new User(
            email: $dto->email,
            phone: $dto->phone,
            password: '',
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            patronymic: $dto->patronymic,
            birthday: $dto->birthday,
            gender: $dto->gender,
        );
        //todo пароль надо на почту отправлять или где-то его сохранять
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->generatePassword()));

        try {
            $this->userRepository->save($user, false);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e);
        }

        return $user;
    }

    /**
     * @throws EmailAlreadyTakenException
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
     * @throws PhoneAlreadyTakenException
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
        //todo после смены идентификатора надо в контроллере обратно сразу залогинить юзера $security->login($user);
    }

    public function updatePassword(User $user, UserUpdatePasswordDto $passwordDto): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $passwordDto->newPassword));
        $this->userRepository->save($user);
        //todo после смены идентификатора надо в контроллере обратно сразу залогинить юзера $security->login($user);
    }

    /**
     * @throws UserAlreadyDeactivatedException
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
           throw new UserNotFoundException($id);
       }

       return $user;
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

    //todo сделать метод публичным, как-то надо обращатсья к созданию пользователя при flush снаружи
    /**
     * @throws UniqueConstraintViolationException
     */
    private function mapUniqueViolation(UniqueConstraintViolationException $e): EmailAlreadyTakenException|PhoneAlreadyTakenException
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
