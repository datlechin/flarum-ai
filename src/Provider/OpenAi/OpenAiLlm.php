<?php

namespace Datlechin\Ai\Provider\OpenAi;

use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Support\HttpTransport;
use Datlechin\Ai\Support\SseStream;
use Generator;

class OpenAiLlm implements LlmClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $model,
    ) {}

    public function complete(array $messages, array $options = []): array
    {
        $response = $this->http->postJson('/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
        ] + $options);

        $data = json_decode((string) $response->getBody(), true) ?: [];

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'model' => $data['model'] ?? $this->model,
            'usage' => $data['usage'] ?? [],
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
        ];
    }

    public function stream(array $messages, array $options = []): Generator
    {
        $response = $this->http->postJsonStream('/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => true,
        ] + $options);

        foreach (SseStream::events($response->getBody()) as $event) {
            $delta = $event['choices'][0]['delta']['content'] ?? null;

            if ($delta !== null && $delta !== '') {
                yield $delta;
            }
        }
    }

    public function model(): string
    {
        return $this->model;
    }
}
