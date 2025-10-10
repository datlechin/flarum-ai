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

use Datlechin\Ai\Events\EmbeddingsGenerated;
use Datlechin\Ai\Events\EmbeddingsStarted;
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

class EmbedController implements RequestHandlerInterface
{
    public function __construct(
        private HttpProviderFactory $factory,
        private Dispatcher $events,
        private SettingsRepositoryInterface $settings
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('datlechin-ai.embed');

        $data = $request->getParsedBody();
        $texts = $data['texts'] ?? [];

        if (empty($texts)) {
            return new JsonResponse(['error' => 'Texts are required'], 400);
        }

        try {
            $provider = $this->factory->createEmbeddingsProvider();

            $this->events->dispatch(new EmbeddingsStarted(
                actor: $actor,
                texts: $texts,
                provider: $provider->getName(),
                model: $provider->getModel()
            ));

            $embeddings = $provider->embedTexts($texts);

            $this->events->dispatch(
                new EmbeddingsGenerated(
                    actor: $actor,
                    texts: $texts,
                    embeddings: $embeddings,
                    provider: $provider->getName(),
                    model: $provider->getModel(),
                    dimension: $provider->getDimension()
                )
            );

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'embeddings' => $embeddings,
                    'model' => $provider->getModel(),
                    'dimension' => $provider->getDimension(),
                ],
            ]);
        } catch (Exception $e) {
            $this->events->dispatch(
                new ProviderFailed(
                    actor: $actor,
                    providerType: 'embeddings',
                    providerName: $this->settings->get('datlechin-ai.provider', 'openai'),
                    operation: 'embed',
                    exception: $e,
                    context: ['texts' => $texts]
                )
            );

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
