<?php

namespace Datlechin\Ai\Provider\Contracts;

interface EmbeddingsClient
{
    /**
     * @param array<int, string> $texts
     * @param array<string, mixed> $options
     * @return array<int, array<int, float>>
     */
    public function embed(array $texts, array $options = []): array;

    public function model(): string;

    public function dimension(): int;
}
