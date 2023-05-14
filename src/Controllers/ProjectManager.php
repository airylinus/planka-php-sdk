<?php

declare(strict_types=1);

namespace Planka\Bridge\Controllers;

use Planka\Bridge\Actions\ProjectManager\ProjectManagerCreateAction;
use Planka\Bridge\Actions\ProjectManager\ProjectManagerDeleteAction;
use Planka\Bridge\Exceptions\ResponseException;
use Planka\Bridge\Exceptions\ValidateException;
use Planka\Bridge\Views\Factory\Project\ProjectManagerDto;
use Planka\Bridge\TransportClients\Client;
use Planka\Bridge\Config;
use Symfony\Component\HttpClient\Exception\ClientException;

final class ProjectManager
{
    public function __construct(
        private readonly Config $config,
        private readonly Client $client
    ) {
    }

    /**
     * 'POST /api/projects/:projectId/managers'
     * @throws ResponseException|ValidateException
     */
    public function add(string $projectId, string $userId): ProjectManagerDto
    {
        try {
            return $this->client->post(new ProjectManagerCreateAction(
                token: $this->config->getAuthToken(),
                projectId: $projectId,
                userId: $userId
            ));
        } catch (ClientException $exception) {
            if ($exception->getCode() === 409) {
                throw new ValidateException('User already joined to project managers');
            }

            throw new ResponseException($exception->getMessage());
        }
    }

    /** 'DELETE /api/project-managers/:id' */
    public function remove(string $managerId): ProjectManagerDto
    {
        return $this->client->delete(new ProjectManagerDeleteAction(
            token: $this->config->getAuthToken(),
            projectManagerId: $managerId
        ));
    }
}