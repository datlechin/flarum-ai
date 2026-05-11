<?php

namespace Datlechin\Ai;

use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderRegistry;
use RuntimeException;

class ModelResolver
{
    public function __construct(
        private readonly Settings $settings,
        private readonly ProviderRegistry $registry,
    ) {}

    public function provider(): Provider
    {
        return $this->registry->get($this->settings->provider());
    }

    public function model(string $capability): string
    {
        $provider = $this->provider();
        $selected = $this->settings->selectedModel($capability);

        if ($selected === Settings::CUSTOM_MODEL_VALUE) {
            return $this->settings->customModel($capability)
                ?? $this->defaultOrFail($provider, $capability);
        }

        if ($selected !== null && $this->isKnownModel($provider, $capability, $selected)) {
            return $selected;
        }

        return $this->defaultOrFail($provider, $capability);
    }

    public function baseUrl(): ?string
    {
        return $this->settings->baseUrl($this->settings->provider());
    }

    private function isKnownModel(Provider $provider, string $capability, string $model): bool
    {
        foreach ($provider->manifest()->models($capability) as $entry) {
            if (($entry['value'] ?? null) === $model) {
                return true;
            }
        }

        return false;
    }

    private function defaultOrFail(Provider $provider, string $capability): string
    {
        $default = $provider->manifest()->defaultModel($capability);

        if ($default !== null) {
            return $default;
        }

        throw new RuntimeException("Provider {$provider->manifest()->name} has no default model for $capability.");
    }
}
