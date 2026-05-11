<?php

namespace Datlechin\Ai\Tests\unit\Provider;

use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Provider\ProviderRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProviderRegistryTest extends TestCase
{
    public function test_registers_and_retrieves_by_manifest_name(): void
    {
        $registry = new ProviderRegistry();
        $registry->register($this->fakeProvider('alpha'));

        $this->assertTrue($registry->has('alpha'));
        $this->assertSame('alpha', $registry->get('alpha')->manifest()->name);
        $this->assertSame(['alpha'], $registry->names());
    }

    public function test_get_throws_for_unregistered_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ProviderRegistry())->get('missing');
    }

    public function test_registering_same_name_replaces_existing_provider(): void
    {
        $registry = new ProviderRegistry();
        $registry->register($this->fakeProvider('alpha'));
        $registry->register($this->fakeProvider('alpha'));

        $this->assertSame(['alpha'], $registry->names());
    }

    public function test_manifests_returns_serializable_array_per_provider(): void
    {
        $registry = new ProviderRegistry();
        $registry->register($this->fakeProvider('alpha'));

        $manifests = $registry->manifests();

        $this->assertArrayHasKey('alpha', $manifests);
        $this->assertSame('alpha', $manifests['alpha']['name']);
    }

    private function fakeProvider(string $name): Provider
    {
        return new class($name) implements Provider {
            public function __construct(private readonly string $name) {}

            public function manifest(): ProviderManifest
            {
                return new ProviderManifest($this->name, ucfirst($this->name));
            }

            public function llm(string $model, ?string $baseUrl = null): LlmClient
            {
                throw new \BadMethodCallException();
            }

            public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient
            {
                throw new \BadMethodCallException();
            }

            public function moderation(string $model, ?string $baseUrl = null): ModerationClient
            {
                throw new \BadMethodCallException();
            }
        };
    }
}
