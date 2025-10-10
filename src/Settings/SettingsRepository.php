<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Settings;

use Flarum\Settings\SettingsRepositoryInterface;

class SettingsRepository
{
    public function __construct(
        private SettingsRepositoryInterface $settings
    ) {}

    public function getProvider(): string
    {
        return $this->settings->get('datlechin-ai.provider', 'openai');
    }

    public function getApiKey(string $provider): ?string
    {
        return $this->settings->get("datlechin-ai.{$provider}.api_key");
    }

    public function getBaseUrl(string $provider): ?string
    {
        return $this->settings->get("datlechin-ai.{$provider}.base_url");
    }

    public function getSelectedModel(string $capability): string
    {
        $provider = $this->getProvider();
        $key = "datlechin-ai.models.selected.{$capability}";

        return $this->settings->get($key, $this->getDefaultModelFor($provider, $capability));
    }

    public function getCustomModel(string $capability): ?string
    {
        return $this->settings->get("datlechin-ai.models.custom.{$capability}");
    }

    public function getCustomBaseUrl(string $capability): ?string
    {
        return $this->settings->get("datlechin-ai.models.custom_base_url.{$capability}");
    }

    private function getDefaultModelFor(string $provider, string $capability): string
    {
        $defaults = [
            'openai' => [
                'text' => 'gpt-4o-mini',
                'embeddings' => 'text-embedding-3-small',
                'moderation' => 'omn-moderation-latest',
            ],
            'gemini' => [
                'text' => 'gemini-1.5-flash',
                'embeddings' => 'text-embedding-004',
                'moderation' => 'gemini-moderation',
            ],
        ];

        return $defaults[$provider][$capability] ?? '';
    }

    public function getEffectiveModel(string $capability): string
    {
        $selected = $this->getSelectedModel($capability);

        if ($selected === '__custom__') {
            return $this->getCustomModel($capability) ?? $selected;
        }

        return $selected;
    }

    public function getEffectiveBaseUrl(string $capability): ?string
    {
        $selected = $this->getSelectedModel($capability);

        if ($selected === '__custom__') {
            $customUrl = $this->getCustomBaseUrl($capability);
            if ($customUrl) {
                return $customUrl;
            }
        }

        $provider = $this->getProvider();
        $baseUrl = $this->getBaseUrl($provider);

        return $baseUrl ?: null;
    }
}
