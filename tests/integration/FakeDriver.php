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

use Datlechin\Ai\Driver\DriverInterface;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;
use Datlechin\Ai\Usage;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;

class FakeDriver implements DriverInterface
{
    public function label(): string
    {
        return 'Fake';
    }

    public function fields(): array
    {
        return [];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        return ['fake-small', 'fake-large'];
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        return new Response("Echo: {$prompt->messages[0]->text}", $model, new Usage(3, 2));
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        yield 'Echo: ';
        yield $prompt->messages[0]->text;

        return new Response("Echo: {$prompt->messages[0]->text}", $model, new Usage(3, 2));
    }
}
