<?php

namespace Datlechin\Ai\Provider\Anthropic;

use BadMethodCallException;
use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Support\HttpTransport;

class AnthropicProvider implements Provider
{
    public function __construct(private readonly string $apiKey) {}

    public function manifest(): ProviderManifest
    {
        return new ProviderManifest(
            name: 'anthropic',
            label: 'Anthropic',
            textModels: [
                ['value' => 'claude-opus-4-7', 'label' => 'Claude Opus 4.7'],
                ['value' => 'claude-sonnet-4-6', 'label' => 'Claude Sonnet 4.6'],
                ['value' => 'claude-haiku-4-5', 'label' => 'Claude Haiku 4.5'],
                ['value' => 'claude-3-7-sonnet-latest', 'label' => 'Claude Sonnet 3.7'],
            ],
            defaults: [
                'text' => 'claude-haiku-4-5',
            ],
            defaultBaseUrl: 'https://api.anthropic.com',
        );
    }

    public function llm(string $model, ?string $baseUrl = null): LlmClient
    {
        return new AnthropicLlm($this->transport($baseUrl), $model);
    }

    public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient
    {
        throw new BadMethodCallException('Anthropic does not provide an embeddings API.');
    }

    public function moderation(string $model, ?string $baseUrl = null): ModerationClient
    {
        throw new BadMethodCallException('Anthropic does not provide a moderation API.');
    }

    private function transport(?string $baseUrl): HttpTransport
    {
        return new HttpTransport(
            baseUrl: $baseUrl ?: $this->manifest()->defaultBaseUrl,
            headers: [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ],
        );
    }
}
