<?php

namespace Datlechin\Ai\Provider\OpenAi;

use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Support\HttpTransport;

class OpenAiModeration implements ModerationClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $model,
    ) {}

    public function classify(string $text, array $options = []): array
    {
        $response = $this->http->postJson('/moderations', [
            'model' => $this->model,
            'input' => $text,
        ] + $options);

        $data = json_decode((string) $response->getBody(), true) ?: [];
        $result = $data['results'][0] ?? [];

        return [
            'flagged' => (bool) ($result['flagged'] ?? false),
            'scores' => array_map(
                fn ($value) => (float) $value,
                $result['category_scores'] ?? [],
            ),
        ];
    }

    public function model(): string
    {
        return $this->model;
    }
}
