<?php

namespace Datlechin\Ai\Provider\OpenAi;

use BadMethodCallException;
use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Support\HttpTransport;

class OpenAiProvider implements Provider
{
    public function __construct(private readonly string $apiKey) {}

    public function manifest(): ProviderManifest
    {
        return new ProviderManifest(
            name: 'openai',
            label: 'OpenAI',
            textModels: [
                ['value' => 'gpt-5', 'label' => 'GPT-5'],
                ['value' => 'gpt-5-mini', 'label' => 'GPT-5 mini'],
                ['value' => 'gpt-5-nano', 'label' => 'GPT-5 nano'],
                ['value' => 'gpt-4o', 'label' => 'GPT-4o'],
                ['value' => 'gpt-4o-mini', 'label' => 'GPT-4o mini'],
            ],
            embeddingModels: [
                ['value' => 'text-embedding-3-large', 'label' => 'Text Embedding 3 Large', 'dimension' => 3072],
                ['value' => 'text-embedding-3-small', 'label' => 'Text Embedding 3 Small', 'dimension' => 1536],
            ],
            moderationModels: [
                ['value' => 'omni-moderation-latest', 'label' => 'Omni Moderation Latest'],
            ],
            defaults: [
                'text' => 'gpt-5-mini',
                'embeddings' => 'text-embedding-3-small',
                'moderation' => 'omni-moderation-latest',
            ],
            defaultBaseUrl: 'https://api.openai.com/v1',
        );
    }

    public function llm(string $model, ?string $baseUrl = null): LlmClient
    {
        return new OpenAiLlm($this->transport($baseUrl), $model);
    }

    public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient
    {
        $dimension = $this->manifest()->dimensionFor($model);

        if ($dimension === null) {
            throw new BadMethodCallException("Unknown OpenAI embedding model: $model.");
        }

        return new OpenAiEmbeddings($this->transport($baseUrl), $model, $dimension);
    }

    public function moderation(string $model, ?string $baseUrl = null): ModerationClient
    {
        return new OpenAiModeration($this->transport($baseUrl), $model);
    }

    private function transport(?string $baseUrl): HttpTransport
    {
        return new HttpTransport(
            baseUrl: $baseUrl ?: $this->manifest()->defaultBaseUrl,
            headers: ['Authorization' => 'Bearer '.$this->apiKey],
        );
    }
}
