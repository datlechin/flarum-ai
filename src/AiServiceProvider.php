<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai;

use Flarum\Foundation\AbstractServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Container\Container;

class AiServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton('datlechin-ai.drivers', fn () => []);

        $this->container->singleton('datlechin-ai.http', fn () => new Client([
            'connect_timeout' => 10,
            'read_timeout' => 60,
            'timeout' => 180,
        ]));

        $this->container->singleton(Ai::class);
    }

    public function boot(Container $container): void
    {
        $container->when(array_values($container->make('datlechin-ai.drivers')))
            ->needs(ClientInterface::class)
            ->give(fn () => $container->make('datlechin-ai.http'));
    }
}
