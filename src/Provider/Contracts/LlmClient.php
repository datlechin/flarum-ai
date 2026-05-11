<?php

namespace Datlechin\Ai\Provider\Contracts;

use Generator;

interface LlmClient
{
    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array{content: string, model: string, usage: array<string, mixed>, finish_reason: string|null}
     */
    public function complete(array $messages, array $options = []): array;

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return Generator<int, string>
     */
    public function stream(array $messages, array $options = []): Generator;

    public function model(): string;
}
