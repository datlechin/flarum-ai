<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\integration\api;

use Datlechin\Ai\Tests\integration\MocksProviderHttp;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class SendsTestPromptTest extends TestCase
{
    use MocksProviderHttp;
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('datlechin-ai');

        $this->prepareDatabase([
            'users' => [$this->normalUser()],
        ]);
    }

    #[Test]
    public function only_admins_can_send_one(): void
    {
        $this->configure();

        $this->assertSame(403, $this->sendTestPrompt(2)->getStatusCode());
    }

    #[Test]
    public function asks_for_setup_when_no_provider_is_configured(): void
    {
        $response = $this->sendTestPrompt();

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('/data/attributes/datlechin-ai.driver', $this->error($response)['source']['pointer']);
    }

    #[Test]
    public function returns_the_models_reply(): void
    {
        $this->configure();

        $this->mockProvider($this->providerJson([
            'id' => 'resp_1',
            'object' => 'response',
            'status' => 'completed',
            'model' => 'gpt-6-luna-2026-09-01',
            'output' => [['type' => 'message', 'role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => 'Hello, Flarum admins!']]]],
            'usage' => ['input_tokens' => 31, 'output_tokens' => 6, 'total_tokens' => 37],
        ]));

        $response = $this->sendTestPrompt();
        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame('Hello, Flarum admins!', $body['text']);
        $this->assertSame('gpt-6-luna-2026-09-01', $body['model']);
        $this->assertSame(31, $body['inputTokens']);
        $this->assertSame(6, $body['outputTokens']);
        $this->assertIsNumeric($body['seconds']);

        $sent = json_decode((string) $this->providerRequests[0]['request']->getBody(), true);
        $this->assertSame('gpt-6-luna', $sent['model']);
        $this->assertSame(200, $sent['max_output_tokens']);
    }

    #[Test]
    public function passes_on_the_providers_error(): void
    {
        $this->configure();

        $this->mockProvider($this->providerJson(['error' => ['message' => 'Incorrect API key provided: sk-test.', 'type' => 'invalid_request_error', 'code' => 'invalid_api_key']], 401));

        $response = $this->sendTestPrompt();

        $this->assertSame(422, $response->getStatusCode());
        // The test app loads no extension locale, but the translator still fills {message} into the bare key.
        $this->assertStringContainsString('Incorrect API key provided: sk-test.', $this->error($response)['detail']);
    }

    private function configure(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-test');
        $this->setting('datlechin-ai.openai.model', 'gpt-6-luna');
    }

    private function sendTestPrompt(int $as = 1): ResponseInterface
    {
        return $this->send($this->request('POST', '/api/datlechin-ai/test', ['authenticatedAs' => $as]));
    }

    private function error(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true)['errors'][0];
    }
}
