<?php
declare(strict_types=1);

namespace App\Customer\Dto;

use App\Customer\Entity\Client;
use App\Identity\Dto\UserSaveDto;
use Symfony\Component\Validator\Constraints as Assert;

final class ClientSaveDto
{
    #[Assert\Valid]
    #[Assert\NotBlank(message: 'Пользователь обязателен')]
    public ?UserSaveDto $userSaveDto = null;

    #[Assert\Length(
        max: 1000,
        maxMessage: 'Заметка должна содержать не более {{ limit }} символов',
    )]
    public ?string $note = null;

    public static function fromClient(Client $client): self
    {
        $self = new self();
        $self->userSaveDto = UserSaveDto::fromUser($client->getUser());
        $self->note = $client->getNote();
        return $self;
    }
}
