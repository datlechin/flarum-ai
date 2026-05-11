<?php

namespace Datlechin\Ai\Event;

use Flarum\User\User;

class TextGenerationStarted
{
    /**
     * @param array<string, mixed> $options Mutable: listeners may modify before the request is dispatched upstream.
     */
    public function __construct(
        public readonly User $actor,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $messageCount,
        public array $options,
    ) {}
}
