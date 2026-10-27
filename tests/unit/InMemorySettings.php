<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\unit;

use Flarum\Settings\SettingsRepositoryInterface;

class InMemorySettings implements SettingsRepositoryInterface
{
    public function __construct(
        private array $settings = []
    ) {
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    public function delete(string $keyLike): void
    {
        unset($this->settings[$keyLike]);
    }
}
