<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Datlechin\Ai\AiServiceProvider;
use Datlechin\Ai\Api\Controller\SendTestPromptController;
use Datlechin\Ai\Api\Resource\AiSettingResource;
use Datlechin\Ai\Driver;
use Datlechin\Ai\Exception\NotConfiguredException;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Extend as AiExtend;
use Flarum\Extend;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(AiServiceProvider::class),

    (new AiExtend\Ai())
        ->driver('openai', Driver\OpenAiDriver::class)
        ->driver('anthropic', Driver\AnthropicDriver::class)
        ->driver('gemini', Driver\GeminiDriver::class)
        ->driver('openai-compatible', Driver\OpenAiCompatibleDriver::class),

    new Extend\ApiResource(AiSettingResource::class),

    (new Extend\Routes('api'))
        ->post('/datlechin-ai/test', 'datlechin-ai.test', SendTestPromptController::class),

    (new Extend\ErrorHandling())
        ->type(NotConfiguredException::class, 'ai_not_configured')
        ->status('ai_not_configured', 503)
        ->type(ProviderException::class, 'ai_provider_error')
        ->status('ai_provider_error', 502),

    (new Extend\Settings())
        ->default('datlechin-ai.driver', 'openai'),
];
