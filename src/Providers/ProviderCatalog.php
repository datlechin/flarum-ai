<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Providers;

class ProviderCatalog
{
    private array $catalog;

    public function __construct()
    {
        $this->reload();
    }

    /**
     * Reload catalog from config file.
     */
    public function reload(): void
    {
        $configPath = __DIR__ . '/../../config/ai_providers.php';

        if (file_exists($configPath)) {
            $this->catalog = require $configPath;
        } else {
            $this->catalog = [];
        }
    }

    /**
     * Get all models for a provider and capability.
     */
    public function getModels(string $provider, string $capability): array
    {
        return $this->catalog[$provider][$capability] ?? [];
    }

    /**
     * Get default model for a provider and capability.
     */
    public function getDefaultModel(string $provider, string $capability): ?string
    {
        $models = $this->getModels($provider, $capability);

        foreach ($models as $model) {
            if ($model['is_default'] ?? false) {
                return $model['value'];
            }
        }

        foreach ($models as $model) {
            if ($model['value'] !== '__custom__') {
                return $model['value'];
            }
        }

        return null;
    }

    /**
     * Get all catalog data.
     */
    public function getAll(): array
    {
        return $this->catalog;
    }

    /**
     * Check if a model is valid for provider and capability.
     */
    public function isValidModel(string $provider, string $capability, string $modelValue): bool
    {
        $models = $this->getModels($provider, $capability);

        foreach ($models as $model) {
            if ($model['value'] === $modelValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model selection is custom.
     */
    public function isCustomModel(string $modelValue): bool
    {
        return $modelValue === '__custom__';
    }
}
