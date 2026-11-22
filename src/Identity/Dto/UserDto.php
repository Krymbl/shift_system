<?php

declare(strict_types=1);

namespace App\Identity\Dto;

use App\Identity\Entity\User;
use App\Identity\Enum\Gender;

final class UserDto
{
    public string $email;
    public string $phone;
    public array $roles;
    public string $lastName;
    public string $firstName;
    public ?string $patronymic = null;
    public ?\DateTimeImmutable $birthday = null;
    public ?Gender $gender = null;

    public static function fromUser(User $user): self
    {
        $self = new self();
        $self->email = $user->getEmail();
        $self->phone = $user->getPhone();
        $self->roles = $user->getRoles();
        $self->lastName = $user->getLastName();
        $self->firstName = $user->getFirstName();
        $self->patronymic = $user->getPatronymic();
        $self->birthday = $user->getBirthday();
        $self->gender = $user->getGender();

        return $self;
    }
}
