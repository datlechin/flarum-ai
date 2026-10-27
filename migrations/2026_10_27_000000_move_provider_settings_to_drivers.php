<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Builder;

// 0.1.x kept one provider with a model per capability. Carry the provider, its key and its text model over.
return [
    'up' => function (Builder $schema) {
        $db = $schema->getConnection();
        $get = fn (string $key) => $db->table('settings')->where('key', $key)->value('value');
        $set = fn (string $key, string $value) => $db->table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);

        $provider = $get('datlechin-ai.provider');

        if (in_array($provider, ['openai', 'anthropic', 'gemini'], true)) {
            $selected = $get('datlechin-ai.models.selected.text');
            $model = $selected === '__custom__' ? $get('datlechin-ai.models.custom.text') : $selected;
            $baseUrl = $get("datlechin-ai.$provider.base_url")
                ?: ($selected === '__custom__' ? $get('datlechin-ai.models.custom_base_url.text') : null);

            $driver = $provider;

            // An OpenAI provider with its own base URL was pointing at an OpenAI-compatible server.
            if ($provider === 'openai' && $baseUrl) {
                $driver = 'openai-compatible';
                $set('datlechin-ai.openai-compatible.base_url', $baseUrl);
                $set('datlechin-ai.openai-compatible.api_key', (string) $get('datlechin-ai.openai.api_key'));
            }

            $set('datlechin-ai.driver', $driver);

            if ($model) {
                $set("datlechin-ai.$driver.model", $model);
            }
        }

        $db->table('settings')
            ->where('key', 'datlechin-ai.provider')
            ->orWhere('key', 'like', 'datlechin-ai.models.%')
            ->orWhereIn('key', ['datlechin-ai.openai.base_url', 'datlechin-ai.anthropic.base_url', 'datlechin-ai.gemini.base_url'])
            ->delete();
    },

    'down' => function (Builder $schema) {
        $db = $schema->getConnection();
        $driver = $db->table('settings')->where('key', 'datlechin-ai.driver')->value('value');

        if (in_array($driver, ['openai', 'anthropic', 'gemini'], true)) {
            $db->table('settings')->updateOrInsert(['key' => 'datlechin-ai.provider'], ['value' => $driver]);
        }

        $db->table('settings')->where('key', 'datlechin-ai.driver')->delete();
    },
];
