<?php

declare(strict_types=1);

namespace App\Identity\Service;

use App\Common\Exception\AbstractBusinessException;
use App\Identity\Dto\UserSaveDto;
use App\Identity\Entity\User;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserChangeActiveStatusException;
use App\Identity\Exception\UserHasRelatedDataException;
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

    /**
     * @throws EmailAlreadyTakenException|PhoneAlreadyTakenException|UniqueConstraintViolationException
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
            throw $this->mapUniqueViolation($e, $dto);
        }

        return $user;
    }

    public function update(User $user, UserSaveDto $dto): void
    {
       $user->setPhone($dto->phone);
       $user->setLastName($dto->lastName);
       $user->setFirstName($dto->firstName);
       $user->setPatronymic($dto->patronymic);
       $user->setBirthday($dto->birthday);
       $user->setGender($dto->gender);

        try {
            $this->userRepository->save($user, false);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e, $dto);
        }
    }

    public function updateEmail(User $user): void
    {
        //todo после смены идентификатора надо в контроллере обратно сразу залогинить юзера $security->login($user);
    }

    public function updatePassword(User $user): void
    {

    }

    /**
     * @throws UserChangeActiveStatusException
     */
    public function deactivate(User $user): void
    {
        if (!$user->isActive()) {
            throw new UserChangeActiveStatusException(false);
        }
        $user->setDeactivatedAt(new \DateTimeImmutable());
        $this->userRepository->save($user);
    }

    /**
     * @throws UserChangeActiveStatusException
     */
    public function activate(User $user): void
    {
        if ($user->isActive()) {
            throw new UserChangeActiveStatusException(true);
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

    public function getById(string $id): ?User
    {
       return $this->userRepository->find($id);
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
    private function mapUniqueViolation(UniqueConstraintViolationException $e, UserSaveDto $dto): AbstractBusinessException
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'users_email_unique') => new EmailAlreadyTakenException($dto->email, $e),
            str_contains($message, 'users_phone_unique') => new PhoneAlreadyTakenException($dto->phone, $e),
            default => throw $e,
        };
    }
}
