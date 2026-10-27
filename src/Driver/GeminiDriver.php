<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Driver;

use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Message;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;
use Datlechin\Ai\Role;
use Datlechin\Ai\Usage;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;

class GeminiDriver extends HttpDriver
{
    public const string URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function label(): string
    {
        return 'datlechin-ai.admin.drivers.gemini';
    }

    public function fields(): array
    {
        return [
            Field::apiKey('datlechin-ai.gemini.api_key', 'datlechin-ai.admin.fields.gemini_api_key_help'),
        ];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        $models = [];
        $pageToken = null;

        do {
            $data = $this->json($this->request('GET', self::URL.'/models', $this->options($settings, [
                'query' => array_filter(['pageSize' => 1000, 'pageToken' => $pageToken]),
            ])));

            foreach ($data['models'] ?? [] as $model) {
                if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true)) {
                    $models[] = substr($model['name'], strlen('models/'));
                }
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken);

        return $models;
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        $response = $this->request('POST', self::URL.'/models/'.rawurlencode($model).':generateContent', $this->options($settings, [
            'json' => $this->payload($prompt),
        ]));

        $chunk = $this->json($response);

        return $this->response($this->text($chunk), $chunk, $model);
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        $response = $this->request('POST', self::URL.'/models/'.rawurlencode($model).':streamGenerateContent', $this->options($settings, [
            'query' => ['alt' => 'sse'],
            'json' => $this->payload($prompt),
            'stream' => true,
        ]));

        $text = '';
        $chunk = [];

        foreach ($this->events($response) as $data) {
            $chunk = $this->decode($data);
            $delta = $this->text($chunk);

            if ($delta !== '') {
                $text .= $delta;
                yield $delta;
            }
        }

        return $this->response($text, $chunk, $model);
    }

    protected function payload(Prompt $prompt): array
    {
        return array_filter([
            'systemInstruction' => ['parts' => [['text' => $prompt->instructions]]],
            'contents' => array_map(fn (Message $message) => [
                'role' => $message->role === Role::Assistant ? 'model' : 'user',
                'parts' => [['text' => $message->text]],
            ], $prompt->messages),
            'generationConfig' => array_filter(['maxOutputTokens' => $prompt->maxOutputTokens]),
        ]);
    }

    protected function text(array $chunk): string
    {
        if ($reason = $chunk['promptFeedback']['blockReason'] ?? null) {
            throw new ProviderException("Gemini blocked the prompt ($reason).");
        }

        $text = '';

        foreach ($chunk['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (! ($part['thought'] ?? false)) {
                $text .= $part['text'] ?? '';
            }
        }

        return $text;
    }

    protected function response(string $text, array $chunk, string $model): Response
    {
        $usage = $chunk['usageMetadata'] ?? [];

        return new Response(
            text: $text,
            model: $chunk['modelVersion'] ?? $model,
            usage: new Usage(
                $usage['promptTokenCount'] ?? 0,
                ($usage['candidatesTokenCount'] ?? 0) + ($usage['thoughtsTokenCount'] ?? 0),
            ),
            truncated: ($chunk['candidates'][0]['finishReason'] ?? null) === 'MAX_TOKENS',
        );
    }

    protected function options(SettingsRepositoryInterface $settings, array $options = []): array
    {
        return array_merge_recursive([
            'headers' => ['x-goog-api-key' => $settings->get('datlechin-ai.gemini.api_key')],
        ], $options);
    }
}
