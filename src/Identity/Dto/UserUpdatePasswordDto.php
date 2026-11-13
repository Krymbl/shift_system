<?php
declare(strict_types=1);

namespace App\Identity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserUpdatePasswordDto {
    #[Assert\NotBlank(message: 'Введите текущий пароль')]
    #[Assert\UserPassword(message: 'Текущий пароль неверный')]
    public ?string $currentPassword = null;

    #[Assert\NotBlank(message: 'Пароль обязателен')]
    #[Assert\Length(
        min: 8,
        max: 100,
        minMessage: 'Пароль должен содержать минимум {{ limit }} символов',
        maxMessage: 'Пароль должен содержать не больше {{ limit }} символов',
    )]
    public ?string $newPassword = null;
}
