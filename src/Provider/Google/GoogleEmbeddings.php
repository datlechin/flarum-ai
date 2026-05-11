<?php

namespace Datlechin\Ai\Provider\Google;

use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Support\HttpTransport;

class GoogleEmbeddings implements EmbeddingsClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $dimension,
    ) {}

    public function embed(array $texts, array $options = []): array
    {
        $requests = array_map(
            fn (string $text) => [
                'model' => "models/{$this->model}",
                'content' => ['parts' => [['text' => $text]]],
            ],
            $texts,
        );

        $response = $this->http->postJson(
            "/v1beta/models/{$this->model}:batchEmbedContents?key={$this->apiKey}",
            ['requests' => $requests] + $options,
        );

        $data = json_decode((string) $response->getBody(), true) ?: [];

        return array_map(
            fn (array $item) => $item['values'] ?? [],
            $data['embeddings'] ?? [],
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
