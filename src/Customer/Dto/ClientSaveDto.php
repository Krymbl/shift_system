<?php
declare(strict_types=1);

namespace App\Customer\Dto;

use App\Identity\Dto\UserSaveDto;

final class ClientSaveDto
{
    public ?int $id = null;
    public ?UserSaveDto $userSaveDto = null;

    public ?string $note = null;
}
