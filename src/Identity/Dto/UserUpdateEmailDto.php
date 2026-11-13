<?php
declare(strict_types=1);

namespace App\Identity\Dto;

use App\Identity\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

#[UniqueEntity(
    fields: ['email'],
    message: 'Пользователь с такой почтой уже существует',
    entityClass: User::class,
    errorPath: 'email',
    identifierFieldNames: ['id'],
)]
final class UserUpdateEmailDto {

    /**
     * Айди пользака
     */
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Почта обязательна')]
    #[Assert\Email(message: 'Введите почту в правильном формате: example@gmail.com')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Почта должна содержать не больше {{ limit }} символов',
    )]
    public ?string $email = null {
        set => mb_strtolower(trim($value));
    }

    #[Assert\NotBlank(message: 'Введите текущий пароль')]
    #[UserPassword(message: 'Текущий пароль неверный')]
    public ?string $currentPassword = null;
}
