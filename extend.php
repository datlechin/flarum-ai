<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Flarum\Extend;
use Datlechin\Ai\Http\Controllers\CatalogController;
use Datlechin\Ai\Http\Controllers\EmbedController;
use Datlechin\Ai\Http\Controllers\GenerateTextController;
use Datlechin\Ai\Http\Controllers\ModerateController;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\ServiceProvider())
        ->register(Datlechin\Ai\AIServiceProvider::class),

    (new Extend\Routes('api'))
        ->post('/ai/generate', 'datlechin-ai.generate', GenerateTextController::class)
        ->post('/ai/embeddings', 'datlechin-ai.embed', EmbedController::class)
        ->post('/ai/moderate', 'datlechin-ai.moderate', ModerateController::class)
        ->get('/ai/provider-catalog', 'datlechin-ai.catalog', CatalogController::class),

    (new Extend\Settings())
        ->default('datlechin-ai.provider', 'openai')
        ->default('datlechin-ai.models.selected.text', 'gpt-4o-mini')
        ->default('datlechin-ai.models.selected.embeddings', 'text-embedding-3-small')
        ->default('datlechin-ai.models.selected.moderation', 'omn-moderation-latest')
        ->serializeToForum('datlechin-ai.provider', 'datlechin-ai.provider'),

    (new Extend\Policy())
        ->globalPolicy(\Datlechin\Ai\Access\GlobalPolicy::class),
];
