<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\integration;

use Datlechin\Ai\Ai;
use Datlechin\Ai\Event\Generated;
use Datlechin\Ai\Exception\NotConfiguredException;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Extend as AiExtend;
use Datlechin\Ai\Prompt;
use Flarum\Testing\integration\TestCase;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\Test;

class AiServiceTest extends TestCase
{
    use MocksProviderHttp;

    private array $generated = [];

    private bool $listening = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('datlechin-ai');
        $this->extend((new AiExtend\Ai())->driver('fake', FakeDriver::class));
    }

    #[Test]
    public function generating_dispatches_an_event_with_the_driver_prompt_and_response(): void
    {
        $this->useFakeDriver();

        $prompt = Prompt::make('Repeat after me.', 'Hi');
        $response = $this->ai()->generate($prompt);

        $this->assertSame('Echo: Hi', $response->text);
        $this->assertSame('fake-small', $response->model);
        $this->assertCount(1, $this->generated);
        $this->assertSame('fake', $this->generated[0]->driver);
        $this->assertSame($prompt, $this->generated[0]->prompt);
        $this->assertSame($response, $this->generated[0]->response);
    }

    #[Test]
    public function streaming_yields_the_text_and_returns_the_response_after_the_event(): void
    {
        $this->useFakeDriver();

        $stream = $this->ai()->stream(Prompt::make('Repeat after me.', 'Hi'));

        $this->assertSame(['Echo: ', 'Hi'], iterator_to_array($stream, false));
        $this->assertSame('Echo: Hi', $stream->getReturn()->text);
        $this->assertCount(1, $this->generated);
        $this->assertSame($stream->getReturn(), $this->generated[0]->response);
    }

    #[Test]
    public function streaming_without_a_provider_throws_before_the_first_chunk(): void
    {
        $this->expectException(NotConfiguredException::class);

        $this->ai()->stream(Prompt::make('', 'Hi'));
    }

    #[Test]
    public function generating_without_a_model_throws(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-test');

        $this->expectException(NotConfiguredException::class);

        $this->ai()->generate(Prompt::make('', 'Hi'));
    }

    #[Test]
    public function a_failed_request_dispatches_no_event(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-test');
        $this->setting('datlechin-ai.openai.model', 'gpt-6-luna');

        $this->mockProvider($this->providerJson(['error' => ['message' => 'The server had an error.']], 500));

        try {
            $this->ai()->generate(Prompt::make('', 'Hi'));
            $this->fail('Expected a ProviderException.');
        } catch (ProviderException $e) {
            $this->assertSame(500, $e->status);
        }

        $this->assertSame([], $this->generated);
    }

    #[Test]
    public function a_driver_from_another_extension_is_listed_and_used(): void
    {
        $this->useFakeDriver();

        $response = $this->send($this->request('GET', '/api/datlechin-ai-settings', ['authenticatedAs' => 1]));
        $attributes = json_decode((string) $response->getBody(), true)['data']['attributes'];

        $this->assertSame(['openai', 'anthropic', 'gemini', 'openai-compatible', 'fake'], array_keys($attributes['drivers']));
        $this->assertSame(['label' => 'Fake', 'fields' => []], $attributes['drivers']['fake']);
        $this->assertSame([], $attributes['errors']);
        $this->assertSame(['ids' => ['fake-small', 'fake-large'], 'error' => null], $attributes['models']);
        $this->assertTrue($this->ai()->isConfigured());
    }

    private function useFakeDriver(): void
    {
        $this->setting('datlechin-ai.driver', 'fake');
        $this->setting('datlechin-ai.fake.model', 'fake-small');
    }

    private function ai(): Ai
    {
        $container = $this->app()->getContainer();

        if (! $this->listening) {
            $container->make(Dispatcher::class)->listen(Generated::class, function (Generated $event) {
                $this->generated[] = $event;
            });

            $this->listening = true;
        }

        return $container->make(Ai::class);
    }
}
