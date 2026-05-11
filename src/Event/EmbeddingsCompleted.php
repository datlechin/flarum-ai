<?php

namespace Datlechin\Ai\Event;

use Flarum\User\User;

class EmbeddingsCompleted
{
    public function __construct(
        public readonly User $actor,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $inputCount,
        public readonly int $dimension,
        public readonly int $latencyMs,
    ) {}
}
