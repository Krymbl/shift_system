<?php

declare(strict_types=1);

namespace App\Identity\Dto;

use App\Common\Util\PhoneNormalizer;
use App\Identity\Entity\User;
use App\Identity\Enum\Gender;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['email'],
    message: 'Пользователь с такой почтой уже существует',
    entityClass: User::class,
    errorPath: 'email',
    identifierFieldNames: ['id'],
)]
#[UniqueEntity(
    fields: ['phone'],
    message: 'Пользователь с таким номером уже существует',
    entityClass: User::class,
    errorPath: 'phone',
    identifierFieldNames: ['id'],
)]
final class UserSaveDto
{
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Почта обязательна', groups: ['create'])]
    #[Assert\Email(message: 'Введите почту в правильном формате: example@gmail.com', groups: ['create'])]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Почта должна содержать не больше {{ limit }} символов',
        groups: ['create']
    )]
    public ?string $email = null {
        set => mb_strtolower(trim($value));
    }

    #[Assert\NotBlank(message: 'Телефон обязателен')]
    #[Assert\Regex(pattern: '/^\+\d{11,15}$/',
        message: 'Проверьте номер телефона, например +7 900 255-45-36 или 8 900 255-45-36')]
    public ?string $phone = null {
        set => PhoneNormalizer::normalize($value);
    }

    #[Assert\NotBlank(message: 'Фамилия обязательна')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Фамилия должна содержать не больше {{ limit }} символов',
    )]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'Имя обязательно')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Имя должно содержать не больше {{ limit }} символов',
    )]
    public ?string $firstName = null;

    #[Assert\Length(
        max: 100,
        maxMessage: 'Отчество должно содержать не больше {{ limit }} символов',
    )]
    public ?string $patronymic = null;

    public ?\DateTimeImmutable $birthday = null;

    public ?Gender $gender = null;

}
