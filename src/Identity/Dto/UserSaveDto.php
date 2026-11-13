<?php

declare(strict_types=1);

namespace App\Identity\Dto;

use App\Identity\Entity\User;
use App\Identity\Enum\Gender;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

//TODO нормализация phone и email до dto
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

//    #[Assert\NotBlank(message: 'Пароль обязателен', groups: ['create'])]
//    #[Assert\Length(
//        min: 8,
//        max: 100,
//        minMessage: 'Пароль должен содержать минимум {{ limit }} символов',
//        maxMessage: 'Пароль должен содержать не больше {{ limit }} символов',
//        groups: ['create']
//    )]
//    public ?string $plainPassword = null;

    #[Assert\NotBlank(message: 'Телефон обязателен')]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: 'Телефон должен содержать минимум {{ limit }} символов',
        maxMessage: 'Телефон должен содержать не больше {{ limit }} символов',
    )]
    public ?string $phone = null;

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
