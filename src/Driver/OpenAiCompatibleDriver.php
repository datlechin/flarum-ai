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
use Datlechin\Ai\Usage;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;

class OpenAiCompatibleDriver extends HttpDriver
{
    public function label(): string
    {
        return 'datlechin-ai.admin.drivers.openai-compatible';
    }

    public function fields(): array
    {
        return [
            new Field(
                setting: 'datlechin-ai.openai-compatible.base_url',
                type: 'url',
                label: 'datlechin-ai.admin.fields.base_url_label',
                help: 'datlechin-ai.admin.fields.base_url_help',
                rules: ['required', 'url:http,https'],
                placeholder: 'https://openrouter.ai/api/v1',
            ),
            Field::apiKey('datlechin-ai.openai-compatible.api_key', 'datlechin-ai.admin.fields.openai_compatible_api_key_help', required: false),
        ];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        $models = $this->json($this->request('GET', $this->url($settings, '/models'), $this->options($settings)))['data'] ?? [];

        $ids = array_column($models, 'id');
        sort($ids);

        return $ids;
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        $response = $this->request('POST', $this->url($settings, '/chat/completions'), $this->options($settings, [
            'json' => $this->payload($model, $prompt),
        ]));

        $data = $this->json($response);

        return new Response(
            text: $data['choices'][0]['message']['content'] ?? '',
            model: $data['model'] ?? $model,
            usage: new Usage($data['usage']['prompt_tokens'] ?? 0, $data['usage']['completion_tokens'] ?? 0),
            truncated: ($data['choices'][0]['finish_reason'] ?? null) === 'length',
        );
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        $response = $this->request('POST', $this->url($settings, '/chat/completions'), $this->options($settings, [
            'json' => $this->payload($model, $prompt) + [
                'stream' => true,
                'stream_options' => ['include_usage' => true],
            ],
            'stream' => true,
        ]));

        $text = '';
        $usage = new Usage();
        $finishReason = null;

        foreach ($this->events($response) as $data) {
            if ($data === '[DONE]') {
                return new Response($text, $model, $usage, $finishReason === 'length');
            }

            $chunk = $this->decode($data);

            if (isset($chunk['error'])) {
                throw new ProviderException($chunk['error']['message'] ?? 'The stream failed.');
            }

            $model = $chunk['model'] ?? $model;
            $finishReason = $chunk['choices'][0]['finish_reason'] ?? $finishReason;

            if (isset($chunk['usage'])) {
                $usage = new Usage($chunk['usage']['prompt_tokens'] ?? 0, $chunk['usage']['completion_tokens'] ?? 0);
            }

            if (($delta = $chunk['choices'][0]['delta']['content'] ?? '') !== '') {
                $text .= $delta;
                yield $delta;
            }
        }

        // Some servers close the stream without sending [DONE].
        return new Response($text, $model, $usage, $finishReason === 'length');
    }

    protected function payload(string $model, Prompt $prompt): array
    {
        $messages = array_map(fn (Message $message) => [
            'role' => $message->role->value,
            'content' => $message->text,
        ], $prompt->messages);

        return array_filter([
            'model' => $model,
            'messages' => [['role' => 'system', 'content' => $prompt->instructions], ...$messages],
            'max_tokens' => $prompt->maxOutputTokens,
        ], fn ($value) => $value !== null);
    }

    protected function url(SettingsRepositoryInterface $settings, string $path): string
    {
        return rtrim((string) $settings->get('datlechin-ai.openai-compatible.base_url'), '/').$path;
    }

    protected function options(SettingsRepositoryInterface $settings, array $options = []): array
    {
        $key = $settings->get('datlechin-ai.openai-compatible.api_key');

        return array_merge_recursive([
            'headers' => $key ? ['Authorization' => "Bearer $key"] : [],
        ], $options);
    }
}
