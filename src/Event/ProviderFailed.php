<?php

namespace Datlechin\Ai\Event;

use Flarum\User\User;
use Throwable;

class ProviderFailed
{
    public function __construct(
        public readonly User $actor,
        public readonly string $provider,
        public readonly string $operation,
        public readonly string $errorClass,
        public readonly string $errorMessage,
    ) {}

    public static function from(User $actor, string $provider, string $operation, Throwable $error): self
    {
        return new self(
            actor: $actor,
            provider: $provider,
            operation: $operation,
            errorClass: $error::class,
            errorMessage: \Datlechin\Ai\Support\ErrorScrubber::scrub($error->getMessage()),
        );
    }
}
