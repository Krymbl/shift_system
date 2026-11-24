<?php

declare(strict_types=1);

namespace App\Organization\Dto;

use App\Identity\Dto\UserSaveDto;
use App\Organization\Entity\Employee;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeSaveDto
{
    #[Assert\NotBlank(message: 'Пользователь обязателен')]
    #[Assert\Valid]
    public ?UserSaveDto $userSaveDto = null;

    public static function fromEmployee(Employee $employee): self
    {
        $self = new self();
        $self->userSaveDto = UserSaveDto::fromUser($employee->getUser());
        return $self;
    }
}
