<?php

declare(strict_types=1);

namespace Planka\Bridge\Actions\Card;

use Planka\Bridge\Contracts\Actions\ResponseResultInterface;
use Planka\Bridge\Contracts\Actions\AuthenticateInterface;
use Planka\Bridge\Contracts\Actions\ActionInterface;
use Planka\Bridge\Traits\AuthenticateTrait;
use Planka\Bridge\Traits\CardHydrateTrait;
use Planka\Bridge\Views\Dto\Card\CardDto;
use Planka\Bridge\Views\Dto\Card\StopWatchDto;
use DateTimeImmutable;
use DateTimeZone;

final class CardTimerAction implements ActionInterface, AuthenticateInterface, ResponseResultInterface
{
    use AuthenticateTrait, CardHydrateTrait;

    private bool $start;

    public function __construct(private readonly CardDto $card, string $token, bool $start)
    {
        $this->start = $start;
        $this->setToken($token);
    }

    public function url(): string
    {
        return "api/cards/{$this->card->id}";
    }

    public function getOptions(): array
    {
        $startedAt = null;
        $total = 0;
        // start timer
        if ($this->start) {
            $stopwatch = $this->tickingWatch();
            $startedAt = $stopwatch->startedAt
                ->setTimezone(new DateTimeZone('UTC'))
                ->format("Y-m-d\TH:i:s.v\Z");
        }
        // stop timer
        if (!$this->start) {
            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $interval = $now->diff($this->card->stopwatch->startedAt);
            $diff = $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
            $total = $this->card->stopwatch->total + $diff;
            $stopwatch = new StopWatchDto(
                null,
                (int)$total
            );
            $this->card->stopwatch = $stopwatch;
        }
        return [
            'json' => [
                'stopwatch' => [
                    "startedAt" => $startedAt,
                    "total" => $total
                ]
            ],
        ];
    }

    private function tickingWatch():StopWatchDto
    {
        $utcZone = new DateTimeZone('UTC');
        // pause condition
        if ($this->card->stopwatch) {
            $diff = $this->card->stopwatch->total;
            return new StopWatchDto(
                (new DateTimeImmutable('now', $utcZone))->modify("-{$diff} seconds"),
                0
            );
        }
        return new StopWatchDto(new DateTimeImmutable('now', $utcZone), 0);
    }
}
