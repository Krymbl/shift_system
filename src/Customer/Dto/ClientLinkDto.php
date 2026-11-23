<?php

declare(strict_types=1);

namespace App\Customer\Dto;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

final class ClientLinkDto
{
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Заметка должна содержать не более {{ limit }} символов',
    )]
    public ?string $note = null;

    #[Assert\NotBlank(message: 'Введите свой текущий пароль')]
    #[UserPassword(message: 'Неверный пароль')]
    public ?string $currentPassword = null;
}
