<?php

namespace Datlechin\Ai\Provider\Google;

use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Support\HttpTransport;
use Datlechin\Ai\Support\SseStream;
use Generator;

class GoogleLlm implements LlmClient
{
    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function complete(array $messages, array $options = []): array
    {
        $response = $this->http->postJson(
            "/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
            $this->payload($messages, $options),
        );

        $data = json_decode((string) $response->getBody(), true) ?: [];

        return [
            'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'model' => $this->model,
            'usage' => $data['usageMetadata'] ?? [],
            'finish_reason' => $data['candidates'][0]['finishReason'] ?? null,
        ];
    }

    public function stream(array $messages, array $options = []): Generator
    {
        $response = $this->http->postJsonStream(
            "/v1beta/models/{$this->model}:streamGenerateContent?key={$this->apiKey}&alt=sse",
            $this->payload($messages, $options),
        );

        foreach (SseStream::events($response->getBody()) as $event) {
            $delta = $event['candidates'][0]['content']['parts'][0]['text'] ?? null;

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
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';

            if ($role === 'system') {
                $system = $content;
                continue;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $content]],
            ];
        }

        $payload = ['contents' => $contents];

        if ($system !== null) {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }

        return $payload + $options;
    }
}
