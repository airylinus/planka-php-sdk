<?php

declare(strict_types=1);

namespace Planka\Bridge\Actions\Card;

use Planka\Bridge\Contracts\Actions\ResponseResultInterface;
use Planka\Bridge\Contracts\Actions\AuthenticateInterface;
use Planka\Bridge\Contracts\Actions\ActionInterface;
use Planka\Bridge\Traits\AuthenticateTrait;
use Planka\Bridge\Traits\CardHydrateTrait;
use Planka\Bridge\Views\Dto\Card\CardDto;

final class CardUpdateAction implements ActionInterface, AuthenticateInterface, ResponseResultInterface
{
    use AuthenticateTrait, CardHydrateTrait;

    public function __construct(
        string $token,
        private readonly string $cardId,
        private readonly CardDto $card,
        private readonly ?int $spentSeconds = null
    ) {
        $this->setToken($token);
    }

    public function url(): string
    {
        return "api/cards/{$this->cardId}";
    }

    public function getOptions(): array
    {
        if ($this->spentSeconds !== null) {
            return [
                'json' => [
                    'stopwatch' => [
                        'startedAt' => null,
                        'total' => $this->getTotalTime(),
                    ],
                ],
            ];
        }

        return [
            'body' => [
                'name' => $this->card->name,
                'description' => $this->card->description,
                'dueDate' => $this->card->dueDate?->format('Y-m-d\TH:i:s.v\Z'),
                'listId' => $this->card->listId,
                'position' => $this->card->position,
            ],
        ];
    }

    private function getTotalTime(): int
    {
        $time = $this->card?->stopwatch->total ?? 0;

        return $time + $this->spentSeconds;
    }
}