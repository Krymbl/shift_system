<?php
declare(strict_types=1);

namespace App\Identity\Dto;

use App\Identity\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['email'],
    message: 'Пользователь с такой почтой уже существует',
    entityClass: User::class,
    errorPath: 'email',
    identifierFieldNames: ['id'],
)]
final class UserUpdateEmailDto {

    #[Assert\NotBlank(message: 'Почта обязательна', groups: ['create'])]
    #[Assert\Email(message: 'Введите почту в правильном формате: example@gmail.com')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Почта должна содержать не больше {{ limit }} символов',
    )]
    public ?string $email = null;
}
