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

class AnthropicDriver extends HttpDriver
{
    public const string URL = 'https://api.anthropic.com/v1';

    public const string VERSION = '2026-10-21';

    // The Messages API requires a limit; this is only used when the caller sets none.
    public const int MAX_TOKENS = 4096;

    public function label(): string
    {
        return 'datlechin-ai.admin.drivers.anthropic';
    }

    public function fields(): array
    {
        return [
            Field::apiKey('datlechin-ai.anthropic.api_key', 'datlechin-ai.admin.fields.anthropic_api_key_help'),
        ];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        $response = $this->request('GET', self::URL.'/models', $this->options($settings, [
            'query' => ['limit' => 1000],
        ]));

        return array_column($this->json($response)['data'] ?? [], 'id');
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        $response = $this->request('POST', self::URL.'/messages', $this->options($settings, [
            'json' => $this->payload($model, $prompt),
        ]));

        $data = $this->json($response);
        $text = implode('', array_column(array_filter($data['content'] ?? [], fn (array $block) => ($block['type'] ?? null) === 'text'), 'text'));

        return new Response(
            text: $text,
            model: $data['model'] ?? $model,
            usage: new Usage($data['usage']['input_tokens'] ?? 0, $data['usage']['output_tokens'] ?? 0),
            truncated: ($data['stop_reason'] ?? null) === 'max_tokens',
        );
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        $response = $this->request('POST', self::URL.'/messages', $this->options($settings, [
            'json' => $this->payload($model, $prompt) + ['stream' => true],
            'stream' => true,
        ]));

        $text = '';
        $inputTokens = $outputTokens = 0;
        $stopReason = null;

        foreach ($this->events($response) as $data) {
            $event = $this->decode($data);

            switch ($event['type'] ?? null) {
                case 'message_start':
                    $model = $event['message']['model'] ?? $model;
                    $inputTokens = $event['message']['usage']['input_tokens'] ?? 0;
                    break;
                case 'content_block_delta':
                    if (($event['delta']['type'] ?? null) === 'text_delta') {
                        $text .= $event['delta']['text'];
                        yield $event['delta']['text'];
                    }
                    break;
                case 'message_delta':
                    $stopReason = $event['delta']['stop_reason'] ?? $stopReason;
                    $outputTokens = $event['usage']['output_tokens'] ?? $outputTokens;
                    break;
                case 'message_stop':
                    return new Response($text, $model, new Usage($inputTokens, $outputTokens), $stopReason === 'max_tokens');
                case 'error':
                    throw new ProviderException($event['error']['message'] ?? 'The stream failed.');
            }
        }

        throw new ProviderException('The stream ended before the response was complete.');
    }

    protected function payload(string $model, Prompt $prompt): array
    {
        return [
            'model' => $model,
            'max_tokens' => $prompt->maxOutputTokens ?? self::MAX_TOKENS,
            'system' => $prompt->instructions,
            'messages' => array_map(fn (Message $message) => [
                'role' => $message->role->value,
                'content' => $message->text,
            ], $prompt->messages),
        ];
    }

    protected function options(SettingsRepositoryInterface $settings, array $options = []): array
    {
        return array_merge_recursive([
            'headers' => [
                'x-api-key' => $settings->get('datlechin-ai.anthropic.api_key'),
                'anthropic-version' => self::VERSION,
            ],
        ], $options);
    }
}
