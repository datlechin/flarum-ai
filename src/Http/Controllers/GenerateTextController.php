<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Http\Controllers;

use Datlechin\Ai\Events\ProviderFailed;
use Datlechin\Ai\Events\TextGenerated;
use Datlechin\Ai\Events\TextGenerationStarted;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Laminas\Diactoros\Response\JsonResponse;
use Datlechin\Ai\Providers\HttpProviderFactory;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateTextController implements RequestHandlerInterface
{
    public function __construct(
        private HttpProviderFactory $factory,
        private SettingsRepositoryInterface $settings,
        private Dispatcher $events
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('datlechin-ai.generate');

        $data = $request->getParsedBody();
        $messages = $data['messages'] ?? [];
        $options = $data['options'] ?? [];
        $stream = $data['stream'] ?? false;

        if (empty($messages)) {
            return new JsonResponse(['error' => 'Messages are required'], 400);
        }

        try {
            $provider = $this->factory->createLlmProvider();

            $this->events->dispatch(new TextGenerationStarted(
                actor: $actor,
                messages: $messages,
                options: $options,
                provider: $provider->getName(),
                model: $provider->getModel(),
                stream: $stream
            ));

            if ($stream) {
                return $this->handleStream($provider, $messages, $options, $actor);
            }

            $result = $provider->complete($messages, $options);

            $this->events->dispatch(
                new TextGenerated(
                    actor: $actor,
                    messages: $messages,
                    result: $result,
                    provider: $provider->getName(),
                    model: $provider->getModel()
                )
            );

            return new JsonResponse([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            $this->events->dispatch(
                new ProviderFailed(
                    actor: $actor,
                    providerType: 'llm',
                    providerName: $this->settings->get('datlechin-ai.provider', 'openai'),
                    operation: $stream ? 'stream' : 'complete',
                    exception: $e,
                    context: ['messages' => $messages, 'options' => $options]
                )
            );

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function handleStream($provider, array $messages, array $options, $actor): ResponseInterface
    {
        $fullContent = '';

        $body = new CallbackStream(function () use ($provider, $messages, $options, $actor, &$fullContent) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            foreach ($provider->stream($messages, $options) as $chunk) {
                $data = "data: " . json_encode(['content' => $chunk]) . "\n\n";
                echo $data;
                flush();
                $fullContent .= $chunk;
            }

            echo "data: [DONE]\n\n";
            flush();

            $this->events->dispatch(
                new TextGenerated(
                    actor: $actor,
                    messages: $messages,
                    result: ['content' => $fullContent, 'streamed' => true],
                    provider: $provider->getName(),
                    model: $provider->getModel()
                )
            );
        });

        $response = new Response($body, 200);
        return $response
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('Connection', 'keep-alive')
            ->withHeader('X-Accel-Buffering', 'no');
    }
}
