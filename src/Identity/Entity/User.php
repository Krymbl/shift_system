<?php

declare(strict_types=1);

namespace App\Identity\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Identity\Enum\Gender;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users', options: ['comment' => 'Пользователи'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //users_email_lower_check
    #[ORM\Column(type: 'string', length: 255, unique: true,
        options: ['comment' => 'Почта - логин. Уникальная, хранится в нижнем регистре'])]
    private string $email;

    #[ORM\Column(type: 'string', length: 30, unique: true,
        options: ['comment' => 'Телефон в едином формате, уникальный'])]
    private string $phone;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Хэш пароля'])]
    private string $password;

    #[ORM\Column(type: 'json', options: ['default' => '[]', 'comment' => 'Роли'])]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'Фамилия'])]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'Имя'])]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100, nullable: true, options: ['comment' => 'Отчество'])]
    private ?string $patronymic = null;

    #[ORM\Column(type: 'date_immutable', nullable: true, options: ['comment' => 'День рождения'])]
    private ?\DateTimeImmutable $birthday = null;

    //users_gender_check
    #[ORM\Column(type: 'string', length: 1, nullable: true, enumType: Gender::class,
        options: ['comment' => 'Пол, m или f'])]
    private ?Gender $gender = null;

    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: ['comment' => 'Дата и время деактивации'])]
    private ?\DateTimeImmutable $deactivatedAt = null;

    public function __construct(
        string $email,
        string $phone,
        string $password,
        string $firstName,
        string $lastName,
        ?string $patronymic = null,
        ?\DateTimeImmutable $birthday = null,
        ?Gender $gender = null,
    ) {
        $this->setEmail($email);
        $this->phone = $phone;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->patronymic = $patronymic;
        $this->birthday = $birthday;
        $this->gender = $gender;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = mb_strtolower(trim($email));
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    //TODO нормализатор телефона
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): void
    {
        $roles = array_filter($roles, static fn(string $role) => $role !== 'ROLE_USER');

        $this->roles = array_values(array_unique($roles));
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(?string $patronymic): void
    {
        $this->patronymic = $patronymic;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeImmutable $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): void
    {
        $this->gender = $gender;
    }

    public function isActive(): bool
    {
        return $this->deactivatedAt === null;
    }

    public function getDeactivatedAt(): ?\DateTimeImmutable
    {
        return $this->deactivatedAt;
    }

    public function setDeactivatedAt(?\DateTimeImmutable $deactivatedAt): void
    {
        $this->deactivatedAt = $deactivatedAt;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
