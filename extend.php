<?php

use Datlechin\Ai\AiServiceProvider;
use Datlechin\Ai\Frontend\AdminPayload;
use Flarum\Extend;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less')
        ->content(AdminPayload::class),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(AiServiceProvider::class),

    (new Extend\Settings())
        ->default('datlechin-ai.provider', 'openai'),
];
