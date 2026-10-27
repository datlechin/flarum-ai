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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Cache\Repository;

trait MocksProviderHttp
{
    protected array $providerRequests = [];

    protected function mockProvider(Response ...$responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->providerRequests));

        $container = $this->app()->getContainer();
        $container->instance('datlechin-ai.http', new Client(['handler' => $stack]));
        // The model list is cached per key, and the test site's cache outlives a single test.
        $container->make(Repository::class)->flush();
    }

    protected function providerJson(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }
}
