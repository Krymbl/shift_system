<?php

declare(strict_types=1);

namespace App\Customer\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Customer\Repository\ClientRepository;
use App\Identity\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(options: ['comment' => 'Клиенты'])]
#[ORM\HasLifecycleCallbacks]
class Client
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Пользователь. Учётная запись клиента'])]
    private User $user;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Записка о клиенте'])]
    private ?string $note = null;

    public function __construct(User $user, ?string $note = null)
    {
        $this->user = $user;
        $this->note = $note;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
}
