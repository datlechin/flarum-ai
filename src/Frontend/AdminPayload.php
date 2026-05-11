<?php

namespace Datlechin\Ai\Frontend;

use Datlechin\Ai\Provider\ProviderRegistry;
use Flarum\Frontend\Document;

class AdminPayload
{
    public function __construct(private readonly ProviderRegistry $registry) {}

    public function __invoke(Document $document): void
    {
        $document->payload['datlechin-ai.providers'] = $this->registry->manifests();
    }
}
