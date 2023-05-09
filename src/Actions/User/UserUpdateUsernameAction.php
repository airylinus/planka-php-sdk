<?php

namespace Planka\Bridge\Actions\User;

use Planka\Bridge\Contracts\Actions\ResponseResultInterface;
use Planka\Bridge\Contracts\Actions\AuthenticateInterface;
use Planka\Bridge\Contracts\Actions\ActionInterface;
use Planka\Bridge\Traits\AuthenticateTrait;
use Planka\Bridge\Traits\UserHydrateTrait;
use Planka\Bridge\Views\Dto\User\UserDto;

final class UserUpdateUsernameAction implements ActionInterface, AuthenticateInterface, ResponseResultInterface
{
    use AuthenticateTrait, UserHydrateTrait;

    public function __construct(
        string $token,
        private readonly UserDto $user,
    ) {
        $this->setToken($token);
    }

    public function url(): string
    {
        return "api/users/{$this->user->id}/username";
    }

    public function getOptions(): array
    {
        return [
            'body' => [
                'username' => $this->user->username,
            ],
        ];
    }
}