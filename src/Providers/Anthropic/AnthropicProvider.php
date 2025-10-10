<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Providers\Anthropic;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Datlechin\Ai\Providers\Contracts\LlmProviderInterface;
use RuntimeException;

class AnthropicProvider implements LlmProviderInterface
{
    private Client $client;
    private string $model;
    private ?string $baseUrl;

    public function __construct(
        string $apiKey,
        string $model = 'claude-3-5-sonnet-20241022',
        ?string $baseUrl = null
    ) {
        $this->model = $model;
        $this->baseUrl = $baseUrl ?? 'https://api.anthropic.com';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 60,
        ]);
    }

    public function complete(array $messages, array $options = []): array
    {
        // Extract system message if present
        $system = null;
        $filteredMessages = [];
        
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $system = $message['content'];
            } else {
                $filteredMessages[] = $message;
            }
        }

        $payload = [
            'model' => $this->model,
            'messages' => $filteredMessages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        // Add other options
        foreach (['temperature', 'top_p', 'top_k'] as $key) {
            if (isset($options[$key])) {
                $payload[$key] = $options[$key];
            }
        }

        try {
            $response = $this->client->post('/v1/messages', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'content' => $data['content'][0]['text'] ?? '',
                'model' => $data['model'] ?? $this->model,
                'usage' => $data['usage'] ?? [],
                'finish_reason' => $data['stop_reason'] ?? null,
            ];
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new RuntimeException('Anthropic API error: ' . $errorBody, 0, $e);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Anthropic API error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function stream(array $messages, array $options = []): \Generator
    {
        // Extract system message if present
        $system = null;
        $filteredMessages = [];
        
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $system = $message['content'];
            } else {
                $filteredMessages[] = $message;
            }
        }

        $payload = [
            'model' => $this->model,
            'messages' => $filteredMessages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'stream' => true,
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        // Add other options
        foreach (['temperature', 'top_p', 'top_k'] as $key) {
            if (isset($options[$key])) {
                $payload[$key] = $options[$key];
            }
        }

        try {
            $response = $this->client->post('/v1/messages', [
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

                // Skip [DONE] message
                if ($data === '[DONE]') {
                    break;
                }

                $json = json_decode($data, true);

                if (!$json) {
                    continue;
                }

                // Handle content_block_delta event
                if (($json['type'] ?? '') === 'content_block_delta') {
                    if (isset($json['delta']['text'])) {
                        yield $json['delta']['text'];
                    }
                }
            }
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new RuntimeException('Anthropic streaming error: ' . $errorBody, 0, $e);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Anthropic streaming error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function chat(array $messages, array $options = []): array
    {
        return $this->complete($messages, $options);
    }

    public function getName(): string
    {
        return 'anthropic';
    }

    public function getModel(): string
    {
        return $this->model;
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
