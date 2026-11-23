<?php

declare(strict_types=1);

namespace App\Organization\Dto;

use App\Customer\Entity\Client;
use App\Identity\Enum\Gender;
use App\Organization\Entity\Employee;

final class EmployeeDto
{
    public string $email;
    public string $phone;
    public string $lastName;
    public string $firstName;
    public ?string $patronymic = null;
    public ?\DateTimeImmutable $birthday = null;
    public ?Gender $gender = null;
    public int $balance;
    private \DateTimeImmutable $hiredAt;
    private ?\DateTimeImmutable $firedAt = null;

    public static function fromEmployee(Employee $employee): self
    {
        $self = new self();

        $user = $employee->getUser();
        $self->email = $user->getEmail();
        $self->phone = $user->getPhone();
        $self->lastName = $user->getLastName();
        $self->firstName = $user->getFirstName();
        $self->patronymic = $user->getPatronymic();
        $self->birthday = $user->getBirthday();
        $self->gender = $user->getGender();

        $self->balance = $employee->getFormattedBalance();
        $self->hiredAt = $employee->getHiredAt();
        $self->firedAt = $employee->getFiredAt();

        return $self;
    }
}
