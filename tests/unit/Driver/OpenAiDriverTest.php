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

use Datlechin\Ai\Driver\OpenAiDriver;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Message;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Tests\unit\InMemorySettings;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class OpenAiDriverTest extends DriverTestCase
{
    private const array RESPONSE = [
        'id' => 'resp_67ccd2bed1ec8190b14f964abc0542670bb6a6b452d3795b',
        'object' => 'response',
        'created_at' => 1741476542,
        'status' => 'completed',
        'model' => 'gpt-6-luna-2026-09-01',
        'output' => [
            ['type' => 'reasoning', 'id' => 'rs_1', 'summary' => []],
            [
                'type' => 'message',
                'id' => 'msg_1',
                'status' => 'completed',
                'role' => 'assistant',
                'content' => [
                    ['type' => 'output_text', 'text' => 'Hello, ', 'annotations' => []],
                    ['type' => 'output_text', 'text' => 'admins!', 'annotations' => []],
                ],
            ],
        ],
        'usage' => ['input_tokens' => 36, 'output_tokens' => 87, 'total_tokens' => 123],
    ];

    #[Test]
    public function generates_through_the_responses_api(): void
    {
        $driver = new OpenAiDriver($this->http($this->json(self::RESPONSE)));

        $response = $driver->generate($this->settings(), 'gpt-6-luna', new Prompt('Be brief.', [Message::user('Hi'), Message::assistant('Hello'), Message::user('Again')]));

        $this->assertSame('POST', $this->sent()->getMethod());
        $this->assertSame('https://api.openai.com/v1/responses', (string) $this->sent()->getUri());
        $this->assertSame('Bearer sk-test', $this->sent()->getHeaderLine('Authorization'));
        $this->assertSame([
            'model' => 'gpt-6-luna',
            'instructions' => 'Be brief.',
            'input' => [
                ['role' => 'user', 'content' => 'Hi'],
                ['role' => 'assistant', 'content' => 'Hello'],
                ['role' => 'user', 'content' => 'Again'],
            ],
            'store' => false,
        ], $this->payload());
        $this->assertResponse('Hello, admins!', 'gpt-6-luna-2026-09-01', 36, 87, false, $response);
    }

    #[Test]
    public function sends_the_output_limit_when_the_prompt_sets_one(): void
    {
        $driver = new OpenAiDriver($this->http($this->json(self::RESPONSE)));

        $driver->generate($this->settings(), 'gpt-6-luna', Prompt::make('Be brief.', 'Hi', 200));

        $this->assertSame(200, $this->payload()['max_output_tokens']);
    }

    #[Test]
    public function an_incomplete_response_is_truncated(): void
    {
        $driver = new OpenAiDriver($this->http($this->json([
            ...self::RESPONSE,
            'status' => 'incomplete',
            'incomplete_details' => ['reason' => 'max_output_tokens'],
        ])));

        $this->assertTrue($driver->generate($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi'))->truncated);
    }

    #[Test]
    public function a_refusal_is_returned_as_text(): void
    {
        $driver = new OpenAiDriver($this->http($this->json([
            ...self::RESPONSE,
            'output' => [['type' => 'message', 'role' => 'assistant', 'content' => [['type' => 'refusal', 'refusal' => 'I can not help with that.']]]],
        ])));

        $this->assertSame('I can not help with that.', $driver->generate($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi'))->text);
    }

    #[Test]
    public function streams_text_deltas_and_returns_the_completed_response(): void
    {
        $completed = json_encode(['type' => 'response.completed', 'response' => self::RESPONSE]);

        $driver = new OpenAiDriver($this->http($this->sse(<<<SSE
            event: response.created
            data: {"type":"response.created","response":{"id":"resp_1","status":"in_progress","output":[]}}

            event: response.output_text.delta
            data: {"type":"response.output_text.delta","item_id":"msg_1","output_index":1,"content_index":0,"delta":"Hello, "}

            event: response.output_text.delta
            data: {"type":"response.output_text.delta","item_id":"msg_1","output_index":1,"content_index":0,"delta":"admins!"}

            event: response.output_text.done
            data: {"type":"response.output_text.done","item_id":"msg_1","text":"Hello, admins!"}

            event: response.completed
            data: $completed


            SSE)));

        [$deltas, $response] = $this->drain($driver->stream($this->settings(), 'gpt-6-luna', Prompt::make('Be brief.', 'Hi')));

        $this->assertTrue($this->payload()['stream']);
        $this->assertSame(['Hello, ', 'admins!'], $deltas);
        $this->assertResponse('Hello, admins!', 'gpt-6-luna-2026-09-01', 36, 87, false, $response);
    }

    #[Test]
    public function a_failed_response_in_the_stream_throws(): void
    {
        $driver = new OpenAiDriver($this->http($this->sse(<<<'SSE'
            event: response.output_text.delta
            data: {"type":"response.output_text.delta","delta":"Hel"}

            event: response.failed
            data: {"type":"response.failed","response":{"status":"failed","error":{"code":"server_error","message":"The model crashed."}}}


            SSE)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('The model crashed.');

        $this->drain($driver->stream($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function an_error_event_in_the_stream_throws(): void
    {
        $driver = new OpenAiDriver($this->http($this->sse(<<<'SSE'
            event: error
            data: {"type":"error","code":"rate_limit_exceeded","message":"Rate limit reached.","param":null}


            SSE)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Rate limit reached.');

        $this->drain($driver->stream($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function a_stream_that_ends_early_throws(): void
    {
        $driver = new OpenAiDriver($this->http($this->sse("event: response.output_text.delta\ndata: {\"type\":\"response.output_text.delta\",\"delta\":\"Hel\"}\n\n")));

        $this->expectException(ProviderException::class);

        $this->drain($driver->stream($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function a_rejected_key_becomes_a_provider_exception_with_openais_message(): void
    {
        $driver = new OpenAiDriver($this->http($this->json([
            'error' => [
                'message' => 'Incorrect API key provided: sk-test. You can find your API key at https://platform.openai.com/account/api-keys.',
                'type' => 'invalid_request_error',
                'param' => null,
                'code' => 'invalid_api_key',
            ],
        ], 401)));

        try {
            $driver->generate($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi'));
            $this->fail('Expected a ProviderException.');
        } catch (ProviderException $e) {
            $this->assertStringStartsWith('Incorrect API key provided', $e->getMessage());
            $this->assertSame(401, $e->status);
        }
    }

    #[Test]
    public function lists_models_newest_first(): void
    {
        $driver = new OpenAiDriver($this->http($this->json([
            'object' => 'list',
            'data' => [
                ['id' => 'gpt-4o', 'object' => 'model', 'created' => 1715367049, 'owned_by' => 'system'],
                ['id' => 'gpt-6-luna', 'object' => 'model', 'created' => 1788000000, 'owned_by' => 'system'],
                ['id' => 'text-embedding-3-small', 'object' => 'model', 'created' => 1705948997, 'owned_by' => 'system'],
            ],
        ])));

        $this->assertSame(['gpt-6-luna', 'gpt-4o', 'text-embedding-3-small'], $driver->models($this->settings()));
        $this->assertSame('GET', $this->sent()->getMethod());
        $this->assertSame('https://api.openai.com/v1/models', (string) $this->sent()->getUri());
        $this->assertSame('Bearer sk-test', $this->sent()->getHeaderLine('Authorization'));
    }

    #[Test]
    public function a_body_that_is_not_json_throws(): void
    {
        $driver = new OpenAiDriver($this->http(new Response(200, [], '<html>Bad gateway</html>')));

        $this->expectException(ProviderException::class);

        $driver->generate($this->settings(), 'gpt-6-luna', Prompt::make('', 'Hi'));
    }

    private function settings(): InMemorySettings
    {
        return new InMemorySettings(['datlechin-ai.openai.api_key' => 'sk-test']);
    }
}
