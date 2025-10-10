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

use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\JsonResponse;
use Datlechin\Ai\Providers\ProviderCatalog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CatalogController implements RequestHandlerInterface
{
    public function __construct(
        private ProviderCatalog $catalog
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('datlechin-ai.viewCatalog');

        $params = $request->getQueryParams();
        $provider = $params['provider'] ?? null;

        if ($provider) {
            $data = [
                'text' => $this->catalog->getModels($provider, 'text'),
                'embeddings' => $this->catalog->getModels($provider, 'embeddings'),
                'moderation' => $this->catalog->getModels($provider, 'moderation'),
            ];
        } else {
            $data = $this->catalog->getAll();
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }
}
