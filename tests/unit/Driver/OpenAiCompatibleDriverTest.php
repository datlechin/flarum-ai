<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\unit\Driver;

use Datlechin\Ai\Driver\OpenAiCompatibleDriver;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Message;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Tests\unit\InMemorySettings;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class OpenAiCompatibleDriverTest extends DriverTestCase
{
    private const array COMPLETION = [
        'id' => 'gen-1761550000-abc',
        'object' => 'chat.completion',
        'created' => 1761550000,
        'model' => 'deepseek/deepseek-v4',
        'choices' => [
            ['index' => 0, 'message' => ['role' => 'assistant', 'content' => 'Hello, admins!'], 'finish_reason' => 'stop'],
        ],
        'usage' => ['prompt_tokens' => 20, 'completion_tokens' => 5, 'total_tokens' => 25],
    ];

    #[Test]
    public function generates_through_chat_completions_at_the_base_url(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->json(self::COMPLETION)));

        $response = $driver->generate($this->settings(), 'deepseek/deepseek-v4', new Prompt('Be brief.', [Message::user('Hi'), Message::assistant('Hello'), Message::user('Again')], 200));

        $this->assertSame('POST', $this->sent()->getMethod());
        $this->assertSame('https://openrouter.ai/api/v1/chat/completions', (string) $this->sent()->getUri());
        $this->assertSame('Bearer sk-or-test', $this->sent()->getHeaderLine('Authorization'));
        $this->assertSame([
            'model' => 'deepseek/deepseek-v4',
            'messages' => [
                ['role' => 'system', 'content' => 'Be brief.'],
                ['role' => 'user', 'content' => 'Hi'],
                ['role' => 'assistant', 'content' => 'Hello'],
                ['role' => 'user', 'content' => 'Again'],
            ],
            'max_tokens' => 200,
        ], $this->payload());
        $this->assertResponse('Hello, admins!', 'deepseek/deepseek-v4', 20, 5, false, $response);
    }

    #[Test]
    public function sends_no_authorization_header_without_a_key(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->json(self::COMPLETION)));

        $driver->generate(new InMemorySettings(['datlechin-ai.openai-compatible.base_url' => 'http://localhost:11434/v1']), 'llama3.2', Prompt::make('', 'Hi'));

        $this->assertSame('http://localhost:11434/v1/chat/completions', (string) $this->sent()->getUri());
        $this->assertFalse($this->sent()->hasHeader('Authorization'));
        $this->assertArrayNotHasKey('max_tokens', $this->payload());
    }

    #[Test]
    public function stopping_at_the_length_limit_is_truncated(): void
    {
        $completion = self::COMPLETION;
        $completion['choices'][0]['finish_reason'] = 'length';

        $driver = new OpenAiCompatibleDriver($this->http($this->json($completion)));

        $this->assertTrue($driver->generate($this->settings(), 'deepseek/deepseek-v4', Prompt::make('', 'Hi'))->truncated);
    }

    #[Test]
    public function streams_text_deltas_and_returns_the_usage_chunk(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->sse(<<<'SSE'
            : OPENROUTER PROCESSING

            data: {"id":"gen-1","object":"chat.completion.chunk","created":1761550000,"model":"deepseek/deepseek-v4","choices":[{"index":0,"delta":{"role":"assistant","content":""},"finish_reason":null}]}

            data: {"id":"gen-1","object":"chat.completion.chunk","created":1761550000,"model":"deepseek/deepseek-v4","choices":[{"index":0,"delta":{"content":"Hello, "},"finish_reason":null}]}

            data: {"id":"gen-1","object":"chat.completion.chunk","created":1761550000,"model":"deepseek/deepseek-v4","choices":[{"index":0,"delta":{"content":"admins!"},"finish_reason":"length"}]}

            data: {"id":"gen-1","object":"chat.completion.chunk","created":1761550000,"model":"deepseek/deepseek-v4","choices":[],"usage":{"prompt_tokens":20,"completion_tokens":5,"total_tokens":25}}

            data: [DONE]


            SSE)));

        [$deltas, $response] = $this->drain($driver->stream($this->settings(), 'deepseek/deepseek-v4', Prompt::make('Be brief.', 'Hi')));

        $this->assertTrue($this->payload()['stream']);
        $this->assertSame(['include_usage' => true], $this->payload()['stream_options']);
        $this->assertSame(['Hello, ', 'admins!'], $deltas);
        $this->assertResponse('Hello, admins!', 'deepseek/deepseek-v4', 20, 5, true, $response);
    }

    #[Test]
    public function a_stream_without_done_still_returns_the_response(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->sse(
            "data: {\"model\":\"llama3.2\",\"choices\":[{\"index\":0,\"delta\":{\"content\":\"Hi\"},\"finish_reason\":\"stop\"}]}\n\n"
        )));

        [$deltas, $response] = $this->drain($driver->stream($this->settings(), 'llama3.2', Prompt::make('', 'Hi')));

        $this->assertSame(['Hi'], $deltas);
        $this->assertResponse('Hi', 'llama3.2', 0, 0, false, $response);
    }

    #[Test]
    public function an_error_chunk_in_the_stream_throws(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->sse(<<<'SSE'
            data: {"choices":[{"index":0,"delta":{"content":"Hel"}}]}

            data: {"error":{"message":"Provider returned error","code":502}}


            SSE)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider returned error');

        $this->drain($driver->stream($this->settings(), 'deepseek/deepseek-v4', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function a_rejected_key_becomes_a_provider_exception_with_the_servers_message(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->json(['error' => ['message' => 'No auth credentials found', 'code' => 401]], 401)));

        try {
            $driver->generate($this->settings(), 'deepseek/deepseek-v4', Prompt::make('', 'Hi'));
            $this->fail('Expected a ProviderException.');
        } catch (ProviderException $e) {
            $this->assertSame('No auth credentials found', $e->getMessage());
            $this->assertSame(401, $e->status);
        }
    }

    #[Test]
    public function reads_an_error_sent_as_a_plain_string(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->json(['error' => "model 'llama9' not found"], 404)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage("model 'llama9' not found");

        $driver->generate($this->settings(), 'llama9', Prompt::make('', 'Hi'));
    }

    #[Test]
    public function falls_back_to_the_status_line_when_the_error_body_is_not_json(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http(new Response(502, [], '<html>Bad gateway</html>')));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('502 Bad Gateway');

        $driver->generate($this->settings(), 'llama3.2', Prompt::make('', 'Hi'));
    }

    #[Test]
    public function lists_models_sorted_by_id(): void
    {
        $driver = new OpenAiCompatibleDriver($this->http($this->json([
            'object' => 'list',
            'data' => [
                ['id' => 'qwen3:8b', 'object' => 'model', 'created' => 1761500000, 'owned_by' => 'library'],
                ['id' => 'llama3.2:latest', 'object' => 'model', 'created' => 1761400000, 'owned_by' => 'library'],
            ],
        ])));

        $this->assertSame(['llama3.2:latest', 'qwen3:8b'], $driver->models($this->settings()));
        $this->assertSame('https://openrouter.ai/api/v1/models', (string) $this->sent()->getUri());
    }

    private function settings(): InMemorySettings
    {
        return new InMemorySettings([
            'datlechin-ai.openai-compatible.base_url' => 'https://openrouter.ai/api/v1/',
            'datlechin-ai.openai-compatible.api_key' => 'sk-or-test',
        ]);
    }
}
