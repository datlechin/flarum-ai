<?php

namespace Datlechin\Ai\Provider\OpenAi;

use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Support\HttpTransport;

class OpenAiEmbeddings implements EmbeddingsClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $model,
        private readonly int $dimension,
    ) {}

    public function embed(array $texts, array $options = []): array
    {
        $response = $this->http->postJson('/embeddings', [
            'model' => $this->model,
            'input' => $texts,
        ] + $options);

        $data = json_decode((string) $response->getBody(), true) ?: [];

        return array_map(
            fn (array $item) => $item['embedding'] ?? [],
            $data['data'] ?? [],
        );
    }

    public function model(): string
    {
        return $this->model;
    }

    public function dimension(): int
    {
        return $this->dimension;
    }
}
