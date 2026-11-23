<?php

declare(strict_types=1);

namespace App\Organization\Dto;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeLinkDto
{
    #[Assert\NotBlank(message: 'Введите свой текущий пароль')]
    #[UserPassword(message: 'Неверный пароль')]
    public ?string $currentPassword = null;
}
