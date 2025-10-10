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

use Datlechin\Ai\Events\ModerationCompleted;
use Datlechin\Ai\Events\ModerationStarted;
use Datlechin\Ai\Events\ProviderFailed;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Laminas\Diactoros\Response\JsonResponse;
use Datlechin\Ai\Providers\HttpProviderFactory;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ModerateController implements RequestHandlerInterface
{
    public function __construct(
        private HttpProviderFactory $factory,
        private Dispatcher $events,
        private SettingsRepositoryInterface $settings
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('datlechin-ai.moderate');

        $data = $request->getParsedBody();
        $text = $data['text'] ?? '';

        if (empty($text)) {
            return new JsonResponse(['error' => 'Text is required'], 400);
        }

        try {
            $provider = $this->factory->createModerationProvider();

            $this->events->dispatch(new ModerationStarted(
                actor: $actor,
                text: $text,
                provider: $provider->getName(),
                model: $provider->getModel()
            ));

            $scores = $provider->classifyText($text);

            $this->events->dispatch(
                new ModerationCompleted(
                    actor: $actor,
                    text: $text,
                    scores: $scores,
                    provider: $provider->getName(),
                    model: $provider->getModel()
                )
            );

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'scores' => $scores,
                    'model' => $provider->getModel(),
                ],
            ]);
        } catch (Exception $e) {
            $this->events->dispatch(
                new ProviderFailed(
                    actor: $actor,
                    providerType: 'moderation',
                    providerName: $this->settings->get('datlechin-ai.provider', 'openai'),
                    operation: 'moderate',
                    exception: $e,
                    context: ['text' => $text]
                )
            );

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
