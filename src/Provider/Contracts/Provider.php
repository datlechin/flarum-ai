<?php

namespace Datlechin\Ai\Provider\Contracts;

use Datlechin\Ai\Provider\ProviderManifest;

interface Provider
{
    public function manifest(): ProviderManifest;

    public function llm(string $model, ?string $baseUrl = null): LlmClient;

    public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient;

    public function moderation(string $model, ?string $baseUrl = null): ModerationClient;
}
