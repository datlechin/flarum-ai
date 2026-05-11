<?php

namespace Datlechin\Ai\Provider\Google;

use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Support\HttpTransport;

class GoogleProvider implements Provider
{
    public function __construct(private readonly string $apiKey) {}

    public function manifest(): ProviderManifest
    {
        return new ProviderManifest(
            name: 'google',
            label: 'Google Gemini',
            textModels: [
                ['value' => 'gemini-2.5-pro', 'label' => 'Gemini 2.5 Pro'],
                ['value' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash'],
                ['value' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash-Lite'],
            ],
            embeddingModels: [
                ['value' => 'gemini-embedding-001', 'label' => 'Gemini Embedding 001', 'dimension' => 3072],
                ['value' => 'text-embedding-004', 'label' => 'Text Embedding 004', 'dimension' => 768],
            ],
            moderationModels: [
                ['value' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash-Lite (safety ratings)'],
            ],
            defaults: [
                'text' => 'gemini-2.5-flash',
                'embeddings' => 'text-embedding-004',
                'moderation' => 'gemini-2.5-flash-lite',
            ],
            defaultBaseUrl: 'https://generativelanguage.googleapis.com',
        );
    }

    public function llm(string $model, ?string $baseUrl = null): LlmClient
    {
        return new GoogleLlm($this->transport($baseUrl), $this->apiKey, $model);
    }

    public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient
    {
        $dimension = $this->manifest()->dimensionFor($model) ?? 768;

        return new GoogleEmbeddings($this->transport($baseUrl), $this->apiKey, $model, $dimension);
    }

    public function moderation(string $model, ?string $baseUrl = null): ModerationClient
    {
        return new GoogleModeration($this->transport($baseUrl), $this->apiKey, $model);
    }

    private function transport(?string $baseUrl): HttpTransport
    {
        return new HttpTransport(
            baseUrl: $baseUrl ?: $this->manifest()->defaultBaseUrl,
        );
    }
}
