<?php

namespace Datlechin\Ai\Event;

use Flarum\User\User;

class TextGenerationCompleted
{
    /**
     * @param array<string, mixed> $usage
     */
    public function __construct(
        public readonly User $actor,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $messageCount,
        public readonly int $outputChars,
        public readonly bool $streamed,
        public readonly int $latencyMs,
        public readonly array $usage = [],
    ) {}
}
