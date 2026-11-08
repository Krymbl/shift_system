<?php

declare(strict_types=1);

namespace App\Identity\Service;

use App\Common\Exception\AbstractBusinessException;
use App\Identity\Dto\UserSaveDto;
use App\Identity\Entity\User;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Repository\UserRepository;
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

        $user->setPassword($this->passwordHasher->hashPassword($user, $this->generatePassword()));

        try {
            $this->userRepository->save($user);
        } catch (UniqueConstraintViolationException $e) {
            throw $this->mapUniqueViolation($e, $dto);
        }

        return $user;
    }

    public function update(User $user, UserSaveDto $dto): void
    {
       $user->setLastName($dto->lastName);
       $user->setFirstName($dto->firstName);
       $user->setPatronymic($dto->patronymic);
       $user->setBirthday($dto->birthday);
       $user->setGender($dto->gender);

       $this->userRepository->save($user);
    }

    public function updateEmail(User $user): void
    {
    }

    public function updatePhone(User $user): void
    {
    }

    public function updatePassword(User $user): void
    {

    }

    public function deactivate(User $user): void
    {
        $user->setDeactivatedAt(new \DateTimeImmutable());
        $this->userRepository->save($user);
    }

    public function activate(User $user): void
    {
        $user->setDeactivatedAt(null);
        $this->userRepository->save($user);
    }

    /**
     * Вызывается только суперадмином в крайних случаях
     */
    public function delete(User $user): void
    {
        //todo кидать предупреждение при обстоятельствах определенных
        $this->userRepository->remove($user);
    }

    public function getById(string $id): ?User
    {
       return $this->userRepository->find($id);
    }

    private function generatePassword(): string
    {
        //todo подумать
        return '';
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
