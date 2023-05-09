<?php

namespace Planka\Bridge\Controllers;

use Planka\Bridge\Actions\User\UserCreateAction;
use Planka\Bridge\Actions\User\UserDeleteAction;
use Planka\Bridge\Actions\User\UserListAction;
use Planka\Bridge\Actions\User\UserUpdateAction;
use Planka\Bridge\Actions\User\UserUpdateAvatarAction;
use Planka\Bridge\Actions\User\UserUpdateEmailAction;
use Planka\Bridge\Actions\User\UserUpdatePasswordAction;
use Planka\Bridge\Actions\User\UserUpdateUsernameAction;
use Planka\Bridge\Actions\User\UserViewAction;
use Planka\Bridge\Config;
use Planka\Bridge\Exceptions\FileExistException;
use Planka\Bridge\TransportClients\Client;
use Planka\Bridge\Views\Dto\User\UserDto;

final class User
{
    public function __construct(
        private readonly Config $config,
        private readonly Client $client
    ) {
    }

    /**
     * 'GET /api/users'
     * @return UserDto[]
     */
    public function list(): array
    {
        return $this->client->get(new UserListAction($this->config->getAuthToken()));
    }

    /** 'POST /api/users' */
    public function create(string $email, string $name, string $password, string $username): UserDto
    {
        return $this->client->post(new UserCreateAction(
            token: $this->config->getAuthToken(),
            email: $email,
            name: $name,
            password: $password,
            username: $username
        ));
    }

    /** 'GET /api/users/:id' */
    public function get(string $id): UserDto
    {
        return $this->client->get(new UserViewAction(token: $this->config->getAuthToken(), id: $id));
    }

    /** 'PATCH /api/users/:id' */
    public function update(UserDto $dto): UserDto
    {
        return $this->client->patch(new UserUpdateAction(token: $this->config->getAuthToken(), user: $dto));
    }

    /** 'PATCH /api/users/:id/email' */
    public function updateEmail(UserDto $dto): UserDto
    {
        return $this->client->patch(new UserUpdateEmailAction(token: $this->config->getAuthToken(), user: $dto));
    }

    /** 'PATCH /api/users/:id/password' */
    public function updatePassword(string $id, string $current, string $new): UserDto
    {
        return $this->client->patch(new UserUpdatePasswordAction(
            token: $this->config->getAuthToken(),
            userId: $id,
            current: $current,
            new: $new
        ));
    }

    /** 'PATCH /api/users/:id/username' */
    public function updateUsername(UserDto $dto): UserDto
    {
        return $this->client->patch(new UserUpdateUsernameAction(token: $this->config->getAuthToken(), user: $dto));
    }

    /**
     * 'POST /api/users/:id/avatar'
     * @throws FileExistException
     */
    public function updateAvatar(UserDto $dto, string $file): UserDto
    {
        return $this->client->post(new UserUpdateAvatarAction(
            token: $this->config->getAuthToken(),
            user: $dto,
            file: $file
        ));
    }

    /** 'DELETE /api/users/:id' */
    public function delete(UserDto $dto): UserDto
    {
        return $this->client->delete(new UserDeleteAction(token: $this->config->getAuthToken(), user: $dto));
    }
}