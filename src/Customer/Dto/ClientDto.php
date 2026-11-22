<?php

declare(strict_types=1);

namespace App\Customer\Dto;

use App\Customer\Entity\Client;
use App\Identity\Entity\User;
use App\Identity\Enum\Gender;

final class ClientDto
{
    public string $email;
    public string $phone;
    public string $lastName;
    public string $firstName;
    public ?string $patronymic = null;
    public ?\DateTimeImmutable $birthday = null;
    public ?Gender $gender = null;
    public ?string $note = null;

    public static function fromClient(Client $client): self
    {
        $self = new self();

        $user = $client->getUser();
        $self->note = $client->getNote();
        $self->email = $user->getEmail();
        $self->phone = $user->getPhone();
        $self->lastName = $user->getLastName();
        $self->firstName = $user->getFirstName();
        $self->patronymic = $user->getPatronymic();
        $self->birthday = $user->getBirthday();
        $self->gender = $user->getGender();

        return $self;
    }
}
