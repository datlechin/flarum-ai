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

class OpenAiDriver extends HttpDriver
{
    public const string URL = 'https://api.openai.com/v1';

    public function label(): string
    {
        return 'datlechin-ai.admin.drivers.openai';
    }

    public function fields(): array
    {
        return [
            Field::apiKey('datlechin-ai.openai.api_key', 'datlechin-ai.admin.fields.openai_api_key_help'),
        ];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        $models = $this->json($this->request('GET', self::URL.'/models', $this->options($settings)))['data'] ?? [];

        usort($models, fn (array $a, array $b) => ($b['created'] ?? 0) <=> ($a['created'] ?? 0));

        return array_column($models, 'id');
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        $response = $this->request('POST', self::URL.'/responses', $this->options($settings, [
            'json' => $this->payload($model, $prompt),
        ]));

        return $this->response($this->json($response), $model);
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        $response = $this->request('POST', self::URL.'/responses', $this->options($settings, [
            'json' => $this->payload($model, $prompt) + ['stream' => true],
            'stream' => true,
        ]));

        foreach ($this->events($response) as $data) {
            $event = $this->decode($data);

            switch ($event['type'] ?? null) {
                case 'response.output_text.delta':
                    yield $event['delta'];
                    break;
                case 'response.completed':
                case 'response.incomplete':
                    return $this->response($event['response'], $model);
                case 'response.failed':
                    throw new ProviderException($event['response']['error']['message'] ?? 'The response failed.');
                case 'error':
                    throw new ProviderException($event['message'] ?? 'The stream failed.');
            }
        }

        throw new ProviderException('The stream ended before the response was complete.');
    }

    protected function payload(string $model, Prompt $prompt): array
    {
        return array_filter([
            'model' => $model,
            'instructions' => $prompt->instructions,
            'input' => array_map(fn (Message $message) => [
                'role' => $message->role->value,
                'content' => $message->text,
            ], $prompt->messages),
            'max_output_tokens' => $prompt->maxOutputTokens,
            // Without this OpenAI keeps every forum post it is sent for 30 days.
            'store' => false,
        ], fn ($value) => $value !== null);
    }

    protected function response(array $data, string $model): Response
    {
        $text = '';

        foreach ($data['output'] ?? [] as $item) {
            foreach (($item['type'] ?? null) === 'message' ? $item['content'] ?? [] : [] as $part) {
                $text .= $part['text'] ?? $part['refusal'] ?? '';
            }
        }

        return new Response(
            text: $text,
            model: $data['model'] ?? $model,
            usage: new Usage($data['usage']['input_tokens'] ?? 0, $data['usage']['output_tokens'] ?? 0),
            truncated: ($data['status'] ?? null) === 'incomplete',
        );
    }

    protected function options(SettingsRepositoryInterface $settings, array $options = []): array
    {
        return array_merge_recursive([
            'headers' => ['Authorization' => 'Bearer '.$settings->get('datlechin-ai.openai.api_key')],
        ], $options);
    }
}
