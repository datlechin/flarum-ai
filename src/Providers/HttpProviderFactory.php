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

use Datlechin\Ai\Providers\Contracts\EmbeddingsProviderInterface;
use Datlechin\Ai\Providers\Contracts\LlmProviderInterface;
use Datlechin\Ai\Providers\Contracts\ModerationProviderInterface;
use Datlechin\Ai\Providers\Anthropic\AnthropicProvider;
use Datlechin\Ai\Providers\Gemini\GeminiProvider;
use Datlechin\Ai\Providers\OpenAI\OpenAIProvider;
use Datlechin\Ai\Settings\SettingsRepository;

class HttpProviderFactory
{
    public function __construct(
        private SettingsRepository $settings,
        private ProviderCatalog $catalog
    ) {}

    /**
     * Create an LLM provider instance.
     */
    public function createLlmProvider(): LlmProviderInterface
    {
        $provider = $this->settings->getProvider();
        $apiKey = $this->settings->getApiKey($provider);
        $model = $this->resolveEffectiveModel('text');
        $baseUrl = $this->settings->getEffectiveBaseUrl('text');

        return match ($provider) {
            'openai' => new OpenAIProvider(
                apiKey: $apiKey,
                model: $model,
                baseUrl: $baseUrl
            ),
            'gemini' => new GeminiProvider(
                apiKey: $apiKey,
                model: $model,
                baseUrl: $baseUrl
            ),
            'anthropic' => new AnthropicProvider(
                apiKey: $apiKey,
                model: $model,
                baseUrl: $baseUrl
            ),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }

    /**
     * Create an embeddings provider instance.
     */
    public function createEmbeddingsProvider(): EmbeddingsProviderInterface
    {
        $provider = $this->settings->getProvider();
        $apiKey = $this->settings->getApiKey($provider);
        $model = $this->resolveEffectiveModel('embeddings');
        $baseUrl = $this->settings->getEffectiveBaseUrl('embeddings');

        return match ($provider) {
            'openai' => new OpenAIProvider(
                apiKey: $apiKey,
                model: 'gpt-4o-mini',
                embeddingModel: $model,
                baseUrl: $baseUrl
            ),
            'gemini' => new GeminiProvider(
                apiKey: $apiKey,
                model: 'gemini-1.5-flash',
                embeddingModel: $model,
                baseUrl: $baseUrl
            ),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }

    /**
     * Create a moderation provider instance.
     */
    public function createModerationProvider(): ModerationProviderInterface
    {
        $provider = $this->settings->getProvider();
        $apiKey = $this->settings->getApiKey($provider);
        $model = $this->resolveEffectiveModel('moderation');
        $baseUrl = $this->settings->getEffectiveBaseUrl('moderation');

        return match ($provider) {
            'openai' => new OpenAIProvider(
                apiKey: $apiKey,
                model: 'gpt-4o-mini',
                embeddingModel: 'text-embedding-3-small',
                moderationModel: $model,
                baseUrl: $baseUrl
            ),
            'gemini' => new GeminiProvider(
                apiKey: $apiKey,
                model: $model,
                embeddingModel: 'text-embedding-004',
                baseUrl: $baseUrl
            ),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }

    /**
     * Resolve the effective model for a capability.
     */
    private function resolveEffectiveModel(string $capability): string
    {
        $selected = $this->settings->getSelectedModel($capability);

        if ($this->catalog->isCustomModel($selected)) {
            $custom = $this->settings->getCustomModel($capability);

            return $custom ?: $selected;
        }

        $provider = $this->settings->getProvider();

        if (! $this->catalog->isValidModel($provider, $capability, $selected)) {
            return $this->catalog->getDefaultModel($provider, $capability) ?? $selected;
        }

        return $selected;
    }
}
