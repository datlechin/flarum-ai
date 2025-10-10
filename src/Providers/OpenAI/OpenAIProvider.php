<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Providers\OpenAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Datlechin\Ai\Providers\Contracts\EmbeddingsProviderInterface;
use Datlechin\Ai\Providers\Contracts\LlmProviderInterface;
use Datlechin\Ai\Providers\Contracts\ModerationProviderInterface;
use Psr\Http\Message\ResponseInterface;

class OpenAIProvider implements LlmProviderInterface, EmbeddingsProviderInterface, ModerationProviderInterface
{
    private Client $client;
    private string $model;
    private string $embeddingModel;
    private string $moderationModel;
    private ?string $baseUrl;

    public function __construct(
        string $apiKey,
        string $model = 'gpt-4o-mini',
        string $embeddingModel = 'text-embedding-3-small',
        string $moderationModel = 'omn-moderation-latest',
        ?string $baseUrl = null
    ) {
        $this->model = $model;
        $this->embeddingModel = $embeddingModel;
        $this->moderationModel = $moderationModel;
        $this->baseUrl = $baseUrl ?? 'https://api.openai.com/v1';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 60,
        ]);
    }

    public function complete(array $messages, array $options = []): array
    {
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], $options);

        try {
            $response = $this->client->post('/chat/completions', [
                'json' => $payload,
            ]);

            return $this->parseResponse($response);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('OpenAI API error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function stream(array $messages, array $options = []): \Generator
    {
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'stream' => true,
        ], $options);

        try {
            $response = $this->client->post('/chat/completions', [
                'json' => $payload,
                'stream' => true,
            ]);

            $body = $response->getBody();

            while (! $body->eof()) {
                $line = $this->readLine($body);

                if (empty($line) || !str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = substr($line, 6);

                if ($data === '[DONE]') {
                    break;
                }

                $json = json_decode($data, true);

                if (isset($json['choices'][0]['delta']['content'])) {
                    yield $json['choices'][0]['delta']['content'];
                }
            }
        } catch (GuzzleException $e) {
            throw new \RuntimeException('OpenAI streaming error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function chat(array $messages, array $options = []): array
    {
        return $this->complete($messages, $options);
    }

    public function embedTexts(array $texts, array $options = []): array
    {
        $payload = array_merge([
            'model' => $this->embeddingModel,
            'input' => $texts,
        ], $options);

        try {
            $response = $this->client->post('/embeddings', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return array_map(fn($item) => $item['embedding'], $data['data'] ?? []);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('OpenAI embeddings error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function classifyText(string $text, array $options = []): array
    {
        $payload = array_merge([
            'model' => $this->moderationModel,
            'input' => $text,
        ], $options);

        try {
            $response = $this->client->post('/moderations', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $result = $data['results'][0] ?? [];

            return [
                'toxicity' => $result['categories']['harassment'] ?? 0.0,
                'nsfw' => $result['categories']['sexual'] ?? 0.0,
                'hate' => $result['categories']['hate'] ?? 0.0,
                'violence' => $result['categories']['violence'] ?? 0.0,
                'self_harm' => $result['categories']['self-harm'] ?? 0.0,
                'scores' => $result['category_scores'] ?? [],
            ];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('OpenAI moderation error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function classifyImage(string $imageUrl, array $options = []): array
    {
        return [
            'nsfw' => 0.0,
            'violence' => 0.0,
            'note' => 'Image moderation not fully supported by OpenAI moderation API',
        ];
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getDimension(): int
    {
        $dimensions = [
            'text-embedding-3-small' => 1536,
            'text-embedding-3-large' => 3072,
            'text-embedding-ada-002' => 1536,
        ];

        return $dimensions[$this->embeddingModel] ?? 1536;
    }

    private function parseResponse(ResponseInterface $response): array
    {
        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'model' => $data['model'] ?? $this->model,
            'usage' => $data['usage'] ?? [],
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
        ];
    }

    private function readLine($stream): string
    {
        $line = '';

        while (! $stream->eof()) {
            $char = $stream->read(1);

            if ($char === "\n") {
                break;
            }

            $line .= $char;
        }

        return trim($line);
    }
}
