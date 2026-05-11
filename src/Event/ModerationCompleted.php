<?php

namespace Datlechin\Ai\Event;

use Flarum\User\User;

class ModerationCompleted
{
    /**
     * @param array<string, float> $scores
     */
    public function __construct(
        public readonly User $actor,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $inputChars,
        public readonly bool $flagged,
        public readonly array $scores,
        public readonly int $latencyMs,
    ) {}
}
