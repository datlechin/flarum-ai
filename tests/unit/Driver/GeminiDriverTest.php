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

use Datlechin\Ai\Driver\GeminiDriver;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Message;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Tests\unit\InMemorySettings;
use PHPUnit\Framework\Attributes\Test;

class GeminiDriverTest extends DriverTestCase
{
    private const array RESPONSE = [
        'candidates' => [
            [
                'content' => [
                    'parts' => [
                        ['text' => 'They want a greeting.', 'thought' => true],
                        ['text' => 'Hello, admins!'],
                    ],
                    'role' => 'model',
                ],
                'finishReason' => 'STOP',
                'index' => 0,
            ],
        ],
        'usageMetadata' => [
            'promptTokenCount' => 12,
            'candidatesTokenCount' => 5,
            'thoughtsTokenCount' => 20,
            'totalTokenCount' => 37,
        ],
        'modelVersion' => 'gemini-3.8-flash',
        'responseId' => 'mAitaLmkHPPlz7IPvtfUqQ4',
    ];

    #[Test]
    public function generates_through_generate_content(): void
    {
        $driver = new GeminiDriver($this->http($this->json(self::RESPONSE)));

        $response = $driver->generate($this->settings(), 'gemini-3.8-flash', new Prompt('Be brief.', [Message::user('Hi'), Message::assistant('Hello'), Message::user('Again')], 200));

        $this->assertSame('POST', $this->sent()->getMethod());
        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent', (string) $this->sent()->getUri());
        $this->assertSame('AIza-test', $this->sent()->getHeaderLine('x-goog-api-key'));
        $this->assertSame([
            'systemInstruction' => ['parts' => [['text' => 'Be brief.']]],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Hi']]],
                ['role' => 'model', 'parts' => [['text' => 'Hello']]],
                ['role' => 'user', 'parts' => [['text' => 'Again']]],
            ],
            'generationConfig' => ['maxOutputTokens' => 200],
        ], $this->payload());
        $this->assertResponse('Hello, admins!', 'gemini-3.8-flash', 12, 25, false, $response);
    }

    #[Test]
    public function leaves_out_the_generation_config_without_an_output_limit(): void
    {
        $driver = new GeminiDriver($this->http($this->json(self::RESPONSE)));

        $driver->generate($this->settings(), 'gemini-3.8-flash', Prompt::make('', 'Hi'));

        $this->assertArrayNotHasKey('generationConfig', $this->payload());
    }

    #[Test]
    public function stopping_at_the_token_limit_is_truncated(): void
    {
        $response = self::RESPONSE;
        $response['candidates'][0]['finishReason'] = 'MAX_TOKENS';

        $driver = new GeminiDriver($this->http($this->json($response)));

        $this->assertTrue($driver->generate($this->settings(), 'gemini-3.8-flash', Prompt::make('', 'Hi'))->truncated);
    }

    #[Test]
    public function a_blocked_prompt_throws(): void
    {
        $driver = new GeminiDriver($this->http($this->json([
            'promptFeedback' => ['blockReason' => 'SAFETY'],
            'usageMetadata' => ['promptTokenCount' => 12, 'totalTokenCount' => 12],
            'modelVersion' => 'gemini-3.8-flash',
        ])));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('SAFETY');

        $driver->generate($this->settings(), 'gemini-3.8-flash', Prompt::make('', 'Hi'));
    }

    #[Test]
    public function streams_text_deltas_and_returns_the_last_chunks_usage(): void
    {
        $driver = new GeminiDriver($this->http($this->sse(
            "data: {\"candidates\": [{\"content\": {\"parts\": [{\"text\": \"Thinking\",\"thought\": true}],\"role\": \"model\"},\"index\": 0}],\"modelVersion\": \"gemini-3.8-flash\"}\r\n\r\n"
            ."data: {\"candidates\": [{\"content\": {\"parts\": [{\"text\": \"Hello, \"}],\"role\": \"model\"},\"index\": 0}],\"usageMetadata\": {\"promptTokenCount\": 12,\"totalTokenCount\": 12},\"modelVersion\": \"gemini-3.8-flash\"}\r\n\r\n"
            ."data: {\"candidates\": [{\"content\": {\"parts\": [{\"text\": \"admins!\"}],\"role\": \"model\"},\"finishReason\": \"MAX_TOKENS\",\"index\": 0}],\"usageMetadata\": {\"promptTokenCount\": 12,\"candidatesTokenCount\": 5,\"thoughtsTokenCount\": 3,\"totalTokenCount\": 20},\"modelVersion\": \"gemini-3.8-flash\"}\r\n\r\n"
        )));

        [$deltas, $response] = $this->drain($driver->stream($this->settings(), 'gemini-3.8-flash', Prompt::make('Be brief.', 'Hi')));

        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:streamGenerateContent?alt=sse', (string) $this->sent()->getUri());
        $this->assertSame(['Hello, ', 'admins!'], $deltas);
        $this->assertResponse('Hello, admins!', 'gemini-3.8-flash', 12, 8, true, $response);
    }

    #[Test]
    public function a_rejected_key_becomes_a_provider_exception_with_geminis_message(): void
    {
        $driver = new GeminiDriver($this->http($this->json([
            'error' => [
                'code' => 400,
                'message' => 'API key not valid. Please pass a valid API key.',
                'status' => 'INVALID_ARGUMENT',
            ],
        ], 400)));

        try {
            $driver->generate($this->settings(), 'gemini-3.8-flash', Prompt::make('', 'Hi'));
            $this->fail('Expected a ProviderException.');
        } catch (ProviderException $e) {
            $this->assertSame('API key not valid. Please pass a valid API key.', $e->getMessage());
            $this->assertSame(400, $e->status);
        }
    }

    #[Test]
    public function reads_the_error_the_streaming_endpoint_wraps_in_an_array(): void
    {
        $driver = new GeminiDriver($this->http($this->json([
            ['error' => ['code' => 401, 'message' => 'Request had invalid authentication credentials.', 'status' => 'UNAUTHENTICATED']],
        ], 401)));

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Request had invalid authentication credentials.');

        $this->drain($driver->stream($this->settings(), 'gemini-3.8-flash', Prompt::make('', 'Hi')));
    }

    #[Test]
    public function lists_only_models_that_generate_content_across_pages(): void
    {
        $driver = new GeminiDriver($this->http(
            $this->json([
                'models' => [
                    ['name' => 'models/gemini-3.8-flash', 'supportedGenerationMethods' => ['generateContent', 'countTokens']],
                    ['name' => 'models/gemini-embedding-001', 'supportedGenerationMethods' => ['embedContent']],
                ],
                'nextPageToken' => 'Ch9tb2RlbHMvZ2VtaW5p',
            ]),
            $this->json([
                'models' => [
                    ['name' => 'models/gemini-3.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
                ],
            ]),
        ));

        $this->assertSame(['gemini-3.8-flash', 'gemini-3.5-flash-lite'], $driver->models($this->settings()));
        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000', (string) $this->sent(0)->getUri());
        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/models?pageSize=1000&pageToken=Ch9tb2RlbHMvZ2VtaW5p', (string) $this->sent(1)->getUri());
        $this->assertSame('AIza-test', $this->sent(1)->getHeaderLine('x-goog-api-key'));
    }

    private function settings(): InMemorySettings
    {
        return new InMemorySettings(['datlechin-ai.gemini.api_key' => 'AIza-test']);
    }
}
