<?php

namespace Datlechin\Ai\Provider\Anthropic;

use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Support\HttpTransport;
use Datlechin\Ai\Support\SseStream;
use Generator;

class AnthropicLlm implements LlmClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $model,
    ) {}

    public function complete(array $messages, array $options = []): array
    {
        $response = $this->http->postJson('/v1/messages', $this->payload($messages, $options));
        $data = json_decode((string) $response->getBody(), true) ?: [];

        return [
            'content' => $data['content'][0]['text'] ?? '',
            'model' => $data['model'] ?? $this->model,
            'usage' => $data['usage'] ?? [],
            'finish_reason' => $data['stop_reason'] ?? null,
        ];
    }

    public function stream(array $messages, array $options = []): Generator
    {
        $response = $this->http->postJsonStream('/v1/messages', $this->payload($messages, $options) + ['stream' => true]);

        foreach (SseStream::events($response->getBody()) as $event) {
            if (($event['type'] ?? null) !== 'content_block_delta') {
                continue;
            }

            $delta = $event['delta']['text'] ?? null;

            if ($delta !== null && $delta !== '') {
                yield $delta;
            }
        }
    }

    public function model(): string
    {
        return $this->model;
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function payload(array $messages, array $options): array
    {
        $system = null;
        $userMessages = [];

        foreach ($messages as $message) {
            if (($message['role'] ?? null) === 'system') {
                $system = $message['content'] ?? null;
                continue;
            }

            $userMessages[] = $message;
        }

        $payload = [
            'model' => $this->model,
            'messages' => $userMessages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
        ];

        if ($system !== null) {
            $payload['system'] = $system;
        }

        foreach (['temperature', 'top_p', 'top_k'] as $key) {
            if (array_key_exists($key, $options)) {
                $payload[$key] = $options[$key];
            }
        }

        return $payload;
    }
}
