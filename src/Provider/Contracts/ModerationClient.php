<?php

namespace Datlechin\Ai\Provider\Contracts;

interface ModerationClient
{
    /**
     * @param array<string, mixed> $options
     * @return array{flagged: bool, scores: array<string, float>}
     */
    public function classify(string $text, array $options = []): array;

    public function model(): string;
}
