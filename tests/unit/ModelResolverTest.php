<?php

namespace Datlechin\Ai\Tests\unit;

use Datlechin\Ai\ModelResolver;
use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Provider\ProviderRegistry;
use Datlechin\Ai\Settings;
use Flarum\Settings\SettingsRepositoryInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ModelResolverTest extends TestCase
{
    public function test_returns_saved_model_when_present_in_manifest(): void
    {
        $resolver = $this->resolver([
            'datlechin-ai.provider' => 'demo',
            'datlechin-ai.models.text' => 'gpt-known',
        ]);

        $this->assertSame('gpt-known', $resolver->model('text'));
    }

    public function test_falls_back_to_default_when_saved_model_is_unknown_to_active_provider(): void
    {
        $resolver = $this->resolver([
            'datlechin-ai.provider' => 'demo',
            'datlechin-ai.models.text' => 'left-over-from-other-provider',
        ]);

        $this->assertSame('gpt-default', $resolver->model('text'));
    }

    public function test_falls_back_to_default_when_no_setting_saved(): void
    {
        $resolver = $this->resolver([
            'datlechin-ai.provider' => 'demo',
        ]);

        $this->assertSame('gpt-default', $resolver->model('text'));
    }

    public function test_returns_custom_model_when_custom_value_selected(): void
    {
        $resolver = $this->resolver([
            'datlechin-ai.provider' => 'demo',
            'datlechin-ai.models.text' => Settings::CUSTOM_MODEL_VALUE,
            'datlechin-ai.models.custom.text' => 'my-private-fine-tune',
        ]);

        $this->assertSame('my-private-fine-tune', $resolver->model('text'));
    }

    public function test_custom_with_empty_custom_value_falls_back_to_default(): void
    {
        $resolver = $this->resolver([
            'datlechin-ai.provider' => 'demo',
            'datlechin-ai.models.text' => Settings::CUSTOM_MODEL_VALUE,
            'datlechin-ai.models.custom.text' => '',
        ]);

        $this->assertSame('gpt-default', $resolver->model('text'));
    }

    public function test_throws_when_capability_has_no_default_and_no_valid_selection(): void
    {
        $this->expectException(RuntimeException::class);

        $this->resolver(['datlechin-ai.provider' => 'demo'])->model('moderation');
    }

    /**
     * @param array<string, string> $stored
     */
    private function resolver(array $stored): ModelResolver
    {
        $repo = new class($stored) implements SettingsRepositoryInterface {
            public function __construct(private array $values) {}

            public function all(): array
            {
                return $this->values;
            }

            public function get(string $key, mixed $default = null): mixed
            {
                return $this->values[$key] ?? $default;
            }

            public function set(string $key, mixed $value): void
            {
                $this->values[$key] = $value;
            }

            public function delete(string $keyLike): void
            {
                unset($this->values[$keyLike]);
            }
        };

        $registry = new ProviderRegistry();
        $registry->register($this->demoProvider());

        return new ModelResolver(new Settings($repo), $registry);
    }

    private function demoProvider(): Provider
    {
        return new class implements Provider {
            public function manifest(): ProviderManifest
            {
                return new ProviderManifest(
                    name: 'demo',
                    label: 'Demo',
                    textModels: [
                        ['value' => 'gpt-known', 'label' => 'Known'],
                        ['value' => 'gpt-default', 'label' => 'Default'],
                    ],
                    defaults: ['text' => 'gpt-default'],
                );
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
