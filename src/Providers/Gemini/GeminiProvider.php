<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Providers\Gemini;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Datlechin\Ai\Providers\Contracts\EmbeddingsProviderInterface;
use Datlechin\Ai\Providers\Contracts\LlmProviderInterface;
use Datlechin\Ai\Providers\Contracts\ModerationProviderInterface;
use RuntimeException;

class GeminiProvider implements LlmProviderInterface, EmbeddingsProviderInterface, ModerationProviderInterface
{
    private Client $client;
    private string $apiKey;
    private string $model;
    private string $embeddingModel;
    private ?string $baseUrl;

    public function __construct(
        string $apiKey,
        string $model = 'gemini-flash-latest',
        string $embeddingModel = 'text-embedding-004',
        ?string $baseUrl = null
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->embeddingModel = $embeddingModel;
        $this->baseUrl = $baseUrl ?? 'https://generativelanguage.googleapis.com';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60,
        ]);
    }

    public function complete(array $messages, array $options = []): array
    {
        $contents = $this->convertMessagesToGeminiFormat($messages);

        $payload = array_merge([
            'contents' => $contents,
        ], $options);

        try {
            $modelName = $this->normalizeModelName($this->model);
            $url = "/v1beta/{$modelName}:generateContent?key={$this->apiKey}";

            $response = $this->client->post($url, [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                'model' => $this->model,
                'usage' => $data['usageMetadata'] ?? [],
                'finish_reason' => $data['candidates'][0]['finishReason'] ?? null,
            ];
        } catch (GuzzleException $e) {
            throw new RuntimeException('Gemini API error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function stream(array $messages, array $options = []): \Generator
    {
        $contents = $this->convertMessagesToGeminiFormat($messages);

        $payload = array_merge([
            'contents' => $contents,
        ], $options);

        try {
            $modelName = $this->normalizeModelName($this->model);
            $url = "/v1beta/{$modelName}:streamGenerateContent?key={$this->apiKey}&alt=sse";

            $response = $this->client->post($url, [
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
                $json = json_decode($data, true);

                if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                    yield $json['candidates'][0]['content']['parts'][0]['text'];
                }
            }
        } catch (GuzzleException $e) {
            throw new RuntimeException('Gemini streaming error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function chat(array $messages, array $options = []): array
    {
        return $this->complete($messages, $options);
    }

    public function embedTexts(array $texts, array $options = []): array
    {
        try {
            $embeddings = [];

            foreach ($texts as $text) {
                $modelName = $this->normalizeModelName($this->embeddingModel);

                $payload = [
                    'model' => $modelName,
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ],
                ];

                $url = "/v1beta/{$modelName}:embedContent?key={$this->apiKey}";

                $response = $this->client->post($url, [
                    'json' => $payload,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $embeddings[] = $data['embedding']['values'] ?? [];
            }

            return $embeddings;
        } catch (GuzzleException $e) {
            throw new RuntimeException('Gemini embeddings error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function classifyText(string $text, array $options = []): array
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Analyze this text for moderation concerns (toxicity, nsfw, hate): " . $text]
                    ]
                ]
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
            ],
        ];

        try {
            $url = "/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = $this->client->post($url, [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $safetyRatings = $data['candidates'][0]['safetyRatings'] ?? [];

            $scores = [];
            foreach ($safetyRatings as $rating) {
                $category = $rating['category'] ?? '';
                $probability = $rating['probability'] ?? 'NEGLIGIBLE';

                $score = match ($probability) {
                    'NEGLIGIBLE' => 0.1,
                    'LOW' => 0.3,
                    'MEDIUM' => 0.6,
                    'HIGH' => 0.9,
                    default => 0.0,
                };

                if (str_contains($category, 'HARASSMENT')) {
                    $scores['toxicity'] = $score;
                } elseif (str_contains($category, 'HATE')) {
                    $scores['hate'] = $score;
                } elseif (str_contains($category, 'SEXUALLY')) {
                    $scores['nsfw'] = $score;
                } elseif (str_contains($category, 'DANGEROUS')) {
                    $scores['violence'] = $score;
                }
            }

            return array_merge([
                'toxicity' => 0.0,
                'nsfw' => 0.0,
                'hate' => 0.0,
                'violence' => 0.0,
            ], $scores);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Gemini moderation error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function classifyImage(string $imageUrl, array $options = []): array
    {
        return [
            'nsfw' => 0.0,
            'violence' => 0.0,
            'note' => 'Image moderation requires implementation',
        ];
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getDimension(): int
    {
        return 768;
    }

    private function convertMessagesToGeminiFormat(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            if ($message['role'] === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']]
                ]
            ];
        }

        return $contents;
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

    private function normalizeModelName(string $modelName): string
    {
        if (str_starts_with($modelName, 'models/')) {
            return $modelName;
        }

        return "models/{$modelName}";
    }
}
