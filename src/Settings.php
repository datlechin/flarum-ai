<?php

namespace Datlechin\Ai;

use Flarum\Settings\SettingsRepositoryInterface;

class Settings
{
    public const PROVIDER = 'datlechin-ai.provider';
    public const CUSTOM_MODEL_VALUE = '__custom__';

    public function __construct(private readonly SettingsRepositoryInterface $repo) {}

    public function provider(): string
    {
        return (string) $this->repo->get(self::PROVIDER, 'openai');
    }

    public function apiKey(string $provider): string
    {
        return (string) $this->repo->get("datlechin-ai.$provider.api_key", '');
    }

    public function baseUrl(string $provider): ?string
    {
        $value = $this->repo->get("datlechin-ai.$provider.base_url");

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    public function selectedModel(string $capability): ?string
    {
        $value = $this->repo->get("datlechin-ai.models.$capability");

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    public function customModel(string $capability): ?string
    {
        $value = $this->repo->get("datlechin-ai.models.custom.$capability");

        return $value !== null && $value !== '' ? (string) $value : null;
    }
}
