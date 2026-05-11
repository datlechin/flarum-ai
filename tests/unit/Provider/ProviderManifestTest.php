<?php

namespace Datlechin\Ai\Tests\unit\Provider;

use Datlechin\Ai\Provider\ProviderManifest;
use PHPUnit\Framework\TestCase;

class ProviderManifestTest extends TestCase
{
    private ProviderManifest $manifest;

    protected function setUp(): void
    {
        $this->manifest = new ProviderManifest(
            name: 'openai',
            label: 'OpenAI',
            textModels: [['value' => 'gpt-5-mini', 'label' => 'GPT-5 mini']],
            embeddingModels: [
                ['value' => 'text-embedding-3-small', 'label' => 'Small', 'dimension' => 1536],
                ['value' => 'text-embedding-3-large', 'label' => 'Large', 'dimension' => 3072],
            ],
            defaults: ['text' => 'gpt-5-mini', 'embeddings' => 'text-embedding-3-small'],
        );
    }

    public function test_supports_returns_true_only_for_declared_capabilities(): void
    {
        $this->assertTrue($this->manifest->supports('text'));
        $this->assertTrue($this->manifest->supports('embeddings'));
        $this->assertFalse($this->manifest->supports('moderation'));
    }

    public function test_default_model_returns_null_when_capability_has_no_default(): void
    {
        $this->assertNull($this->manifest->defaultModel('moderation'));
        $this->assertSame('gpt-5-mini', $this->manifest->defaultModel('text'));
    }

    public function test_dimension_for_returns_declared_dimension(): void
    {
        $this->assertSame(1536, $this->manifest->dimensionFor('text-embedding-3-small'));
        $this->assertSame(3072, $this->manifest->dimensionFor('text-embedding-3-large'));
        $this->assertNull($this->manifest->dimensionFor('unknown-model'));
    }

    public function test_to_array_lists_only_supported_capabilities(): void
    {
        $array = $this->manifest->toArray();

        $this->assertSame(['text', 'embeddings'], $array['capabilities']);
        $this->assertArrayHasKey('moderation', $array['models']);
        $this->assertSame([], $array['models']['moderation']);
    }
}
