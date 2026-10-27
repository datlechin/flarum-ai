<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Driver;

use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;

interface DriverInterface
{
    public function label(): string;

    public function fields(): array;

    public function models(SettingsRepositoryInterface $settings): array;

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response;

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator;
}
