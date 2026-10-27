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

use Datlechin\Ai\Driver\AnthropicDriver;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Message;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Tests\unit\InMemorySettings;
use PHPUnit\Framework\Attributes\Test;

class AnthropicDriverTest extends DriverTestCase
{
    private const array MESSAGE = [
        'id' => 'msg_013Zva2CMHLNnXjNJJKqJ2EF',
        'type' => 'message',
        'role' => 'assistant',
        'model' => 'claude-haiku-5-5',
        'content' => [
            ['type' => 'thinking', 'thinking' => 'They want a greeting.', 'signature' => 'EqQBCgIYAhIM'],
            ['type' => 'text', 'text' => 'Hello, admins!'],
        ],
        'stop_reason' => 'end_turn',
        'stop_sequence' => null,
        'usage' => ['input_tokens' => 25, 'output_tokens' => 9],
    ];

    #[Test]
    public function generates_through_the_messages_api(): void
    {
        $driver = new AnthropicDriver($this->http($this->json(self::MESSAGE)));

        $response = $driver->generate($this->settings(), 'claude-haiku-5-5', new Prompt('Be brief.', [Message::user('Hi'), Message::assistant('Hello'), Message::user('Again')]));

        $this->assertSame('POST', $this->sent()->getMethod());
        $this->assertSame('https://api.anthropic.com/v1/messages', (string) $this->sent()->getUri());
        $this->assertSame('sk-ant-test', $this->sent()->getHeaderLine('x-api-key'));
        $this->assertSame('2026-10-21', $this->sent()->getHeaderLine('anthropic-version'));
        $this->assertSame([
            'model' => 'claude-haiku-5-5',
            'max_tokens' => 4096,
            'system' => 'Be brief.',
            'messages' => [
                ['role' => 'user', 'content' => 'Hi'],
                ['role' => 'assistant', 'content' => 'Hello'],
                ['role' => 'user', 'content' => 'Again'],
            ],
        ], $this->payload());
        $this->assertResponse('Hello, admins!', 'claude-haiku-5-5', 25, 9, false, $response);
    }

    #[Test]
    public function sends_the_prompts_output_limit_instead_of_the_default(): void
    {
        $driver = new AnthropicDriver($this->http($this->json(self::MESSAGE)));

        $driver->generate($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi', 200));

        $this->assertSame(200, $this->payload()['max_tokens']);
    }

    #[Test]
    public function never_sends_sampling_parameters(): void
    {
        $driver = new AnthropicDriver($this->http($this->json(self::MESSAGE)));

        $driver->generate($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi'));

        $this->assertEmpty(array_intersect_key($this->payload(), array_flip(['temperature', 'top_p', 'top_k'])));
    }

    #[Test]
    public function stopping_at_the_token_limit_is_truncated(): void
    {
        $driver = new AnthropicDriver($this->http($this->json([...self::MESSAGE, 'stop_reason' => 'max_tokens'])));

        $this->assertTrue($driver->generate($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi'))->truncated);
    }

    #[Test]
    public function streams_text_deltas_and_returns_the_usage(): void
    {
        $driver = new AnthropicDriver($this->http($this->sse(<<<'SSE'
            event: message_start
            data: {"type":"message_start","message":{"id":"msg_1","type":"message","role":"assistant","content":[],"model":"claude-haiku-5-5","stop_reason":null,"stop_sequence":null,"usage":{"input_tokens":25,"output_tokens":1}}}

            event: content_block_start
            data: {"type":"content_block_start","index":0,"content_block":{"type":"thinking","thinking":""}}

            event: content_block_delta
            data: {"type":"content_block_delta","index":0,"delta":{"type":"thinking_delta","thinking":"A greeting."}}

            event: content_block_stop
            data: {"type":"content_block_stop","index":0}

            event: ping
            data: {"type": "ping"}

            event: content_block_start
            data: {"type":"content_block_start","index":1,"content_block":{"type":"text","text":""}}

            event: content_block_delta
            data: {"type":"content_block_delta","index":1,"delta":{"type":"text_delta","text":"Hello, "}}

            event: content_block_delta
            data: {"type":"content_block_delta","index":1,"delta":{"type":"text_delta","text":"admins!"}}

            event: content_block_stop
            data: {"type":"content_block_stop","index":1}

            event: message_delta
            data: {"type":"message_delta","delta":{"stop_reason":"max_tokens","stop_sequence":null},"usage":{"output_tokens":15}}

            event: message_stop
            data: {"type":"message_stop"}


            SSE)));

        [$deltas, $response] = $this->drain($driver->stream($this->settings(), 'claude-haiku-5-5', Prompt::make('Be brief.', 'Hi')));

        $this->assertTrue($this->payload()['stream']);
        $this->assertSame(['Hello, ', 'admins!'], $deltas);
        $this->assertResponse('Hello, admins!', 'claude-haiku-5-5', 25, 15, true, $response);
    }

    #[Test]
    public function an_error_event_in_the_stream_throws(): void
    {
        $driver = new AnthropicDriver($this->http($this->sse(<<<'SSE'
            event: message_start
            data: {"type":"message_start","message":{"model":"claude-haiku-5-5","usage":{"input_tokens":25}}}

            event: error
            data: {"type":"error","error":{"type":"overloaded_error","message":"Overloaded"}}


            SSE)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Overloaded');

        $this->drain($driver->stream($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function a_stream_that_ends_before_message_stop_throws(): void
    {
        $driver = new AnthropicDriver($this->http($this->sse("event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"Hel\"}}\n\n")));

        $this->expectException(ProviderException::class);

        $this->drain($driver->stream($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function a_rejected_key_becomes_a_provider_exception_with_anthropics_message(): void
    {
        $driver = new AnthropicDriver($this->http($this->json([
            'type' => 'error',
            'error' => ['type' => 'authentication_error', 'message' => 'invalid x-api-key'],
            'request_id' => 'req_011CSHoEeqs5C35K2UUqR7Fy',
        ], 401)));

        try {
            $driver->generate($this->settings(), 'claude-haiku-5-5', Prompt::make('', 'Hi'));
            $this->fail('Expected a ProviderException.');
        } catch (ProviderException $e) {
            $this->assertSame('invalid x-api-key', $e->getMessage());
            $this->assertSame(401, $e->status);
        }
    }

    #[Test]
    public function lists_models(): void
    {
        $driver = new AnthropicDriver($this->http($this->json([
            'data' => [
                ['type' => 'model', 'id' => 'claude-opus-5-5', 'display_name' => 'Claude Opus 5.5', 'created_at' => '2026-09-01T00:00:00Z'],
                ['type' => 'model', 'id' => 'claude-haiku-5-5', 'display_name' => 'Claude Haiku 5.5', 'created_at' => '2026-08-01T00:00:00Z'],
            ],
            'has_more' => false,
            'first_id' => 'claude-opus-5-5',
            'last_id' => 'claude-haiku-5-5',
        ])));

        $this->assertSame(['claude-opus-5-5', 'claude-haiku-5-5'], $driver->models($this->settings()));
        $this->assertSame('GET', $this->sent()->getMethod());
        $this->assertSame('https://api.anthropic.com/v1/models?limit=1000', (string) $this->sent()->getUri());
        $this->assertSame('sk-ant-test', $this->sent()->getHeaderLine('x-api-key'));
        $this->assertSame('2026-10-21', $this->sent()->getHeaderLine('anthropic-version'));
    }

    private function settings(): InMemorySettings
    {
        return new InMemorySettings(['datlechin-ai.anthropic.api_key' => 'sk-ant-test']);
    }
}
