<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Extend;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extension\Extension;
use Illuminate\Contracts\Container\Container;

class Ai implements ExtenderInterface
{
    private array $drivers = [];

    public function driver(string $name, string $driver): self
    {
        $this->drivers[$name] = $driver;

        return $this;
    }

    public function extend(Container $container, ?Extension $extension = null): void
    {
        $container->extend('datlechin-ai.drivers', fn (array $drivers) => array_merge($drivers, $this->drivers));
    }
}
