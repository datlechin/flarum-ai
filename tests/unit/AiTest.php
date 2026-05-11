<?php

namespace Datlechin\Ai\Tests\unit;

use Datlechin\Ai\Ai;
use Datlechin\Ai\Event\EmbeddingsCompleted;
use Datlechin\Ai\Event\ModerationCompleted;
use Datlechin\Ai\Event\ProviderFailed;
use Datlechin\Ai\Event\TextGenerationCompleted;
use Datlechin\Ai\Event\TextGenerationStarted;
use Datlechin\Ai\ModelResolver;
use Datlechin\Ai\Provider\Contracts\EmbeddingsClient;
use Datlechin\Ai\Provider\Contracts\LlmClient;
use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Provider\Contracts\Provider;
use Datlechin\Ai\Provider\ProviderManifest;
use Datlechin\Ai\Provider\ProviderRegistry;
use Datlechin\Ai\Settings;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AiTest extends TestCase
{
    public function test_generate_dispatches_started_and_completed_events(): void
    {
        $captured = [];
        $ai = $this->ai($captured, llmComplete: ['content' => 'hello world', 'model' => 'demo-1', 'usage' => ['tokens' => 5], 'finish_reason' => 'stop']);

        $result = $ai->generate(new User(), [['role' => 'user', 'content' => 'hi']]);

        $this->assertSame('hello world', $result['content']);
        $this->assertContainsOnlyInstancesOf(
            TextGenerationStarted::class,
            [$captured[0]],
        );
        $this->assertInstanceOf(TextGenerationCompleted::class, $captured[1]);
        $this->assertSame(11, $captured[1]->outputChars);
        $this->assertFalse($captured[1]->streamed);
    }

    public function test_failed_generate_dispatches_provider_failed_then_rethrows(): void
    {
        $captured = [];
        $ai = $this->ai($captured, llmThrow: new RuntimeException('upstream blew up'));

        try {
            $ai->generate(new User(), [['role' => 'user', 'content' => 'hi']]);
            $this->fail('Expected exception was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertSame('upstream blew up', $e->getMessage());
        }

        $this->assertInstanceOf(TextGenerationStarted::class, $captured[0]);
        $this->assertInstanceOf(ProviderFailed::class, $captured[1]);
        $this->assertSame('text', $captured[1]->operation);
    }

    public function test_stream_yields_chunks_and_dispatches_completion_with_streamed_true(): void
    {
        $captured = [];
        $ai = $this->ai($captured, llmStreamChunks: ['ab', 'cd', 'ef']);

        $chunks = iterator_to_array($ai->stream(new User(), [['role' => 'user', 'content' => 'go']]), false);

        $this->assertSame(['ab', 'cd', 'ef'], $chunks);
        $this->assertInstanceOf(TextGenerationCompleted::class, end($captured));
        $this->assertTrue(end($captured)->streamed);
        $this->assertSame(6, end($captured)->outputChars);
    }

    public function test_embed_returns_vectors_and_dispatches_completed(): void
    {
        $captured = [];
        $ai = $this->ai($captured, embedReturn: [[0.1, 0.2], [0.3, 0.4]]);

        $vectors = $ai->embed(new User(), ['a', 'b']);

        $this->assertSame([[0.1, 0.2], [0.3, 0.4]], $vectors);
        $this->assertInstanceOf(EmbeddingsCompleted::class, $captured[0]);
        $this->assertSame(2, $captured[0]->inputCount);
    }

    public function test_moderate_returns_classification_and_dispatches_completed(): void
    {
        $captured = [];
        $ai = $this->ai($captured, moderateReturn: ['flagged' => true, 'scores' => ['toxic' => 0.9]]);

        $result = $ai->moderate(new User(), 'bad stuff');

        $this->assertTrue($result['flagged']);
        $this->assertInstanceOf(ModerationCompleted::class, $captured[0]);
        $this->assertTrue($captured[0]->flagged);
        $this->assertSame(9, $captured[0]->inputChars);
    }

    /**
     * @param list<object> $captured Reference accumulator for dispatched events.
     */
    private function ai(
        array &$captured,
        ?array $llmComplete = null,
        ?array $llmStreamChunks = null,
        ?\Throwable $llmThrow = null,
        ?array $embedReturn = null,
        ?array $moderateReturn = null,
    ): Ai {
        $repo = new class implements SettingsRepositoryInterface {
            public function all(): array { return []; }
            public function get(string $key, mixed $default = null): mixed { return $key === 'datlechin-ai.provider' ? 'demo' : $default; }
            public function set(string $key, mixed $value): void {}
            public function delete(string $keyLike): void {}
        };

        $provider = new class($llmComplete, $llmStreamChunks, $llmThrow, $embedReturn, $moderateReturn) implements Provider {
            public function __construct(
                private ?array $llmComplete,
                private ?array $llmStreamChunks,
                private ?\Throwable $llmThrow,
                private ?array $embedReturn,
                private ?array $moderateReturn,
            ) {}

            public function manifest(): ProviderManifest
            {
                return new ProviderManifest(
                    name: 'demo',
                    label: 'Demo',
                    textModels: [['value' => 'demo-1', 'label' => 'Demo 1']],
                    embeddingModels: [['value' => 'demo-emb', 'label' => 'Demo Emb', 'dimension' => 2]],
                    moderationModels: [['value' => 'demo-mod', 'label' => 'Demo Mod']],
                    defaults: ['text' => 'demo-1', 'embeddings' => 'demo-emb', 'moderation' => 'demo-mod'],
                );
            }

            public function llm(string $model, ?string $baseUrl = null): LlmClient
            {
                return new class($this->llmComplete, $this->llmStreamChunks, $this->llmThrow) implements LlmClient {
                    public function __construct(private ?array $complete, private ?array $chunks, private ?\Throwable $throw) {}
                    public function complete(array $messages, array $options = []): array
                    {
                        if ($this->throw) throw $this->throw;
                        return $this->complete ?? ['content' => '', 'model' => '', 'usage' => [], 'finish_reason' => null];
                    }
                    public function stream(array $messages, array $options = []): \Generator
                    {
                        foreach ($this->chunks ?? [] as $chunk) yield $chunk;
                    }
                    public function model(): string { return 'demo-1'; }
                };
            }

            public function embeddings(string $model, ?string $baseUrl = null): EmbeddingsClient
            {
                return new class($this->embedReturn) implements EmbeddingsClient {
                    public function __construct(private ?array $value) {}
                    public function embed(array $texts, array $options = []): array { return $this->value ?? []; }
                    public function model(): string { return 'demo-emb'; }
                    public function dimension(): int { return 2; }
                };
            }

            public function moderation(string $model, ?string $baseUrl = null): ModerationClient
            {
                return new class($this->moderateReturn) implements ModerationClient {
                    public function __construct(private ?array $value) {}
                    public function classify(string $text, array $options = []): array { return $this->value ?? ['flagged' => false, 'scores' => []]; }
                    public function model(): string { return 'demo-mod'; }
                };
            }
        };

        $registry = new ProviderRegistry();
        $registry->register($provider);

        $resolver = new ModelResolver(new Settings($repo), $registry);

        $events = new class($captured) implements Dispatcher {
            public function __construct(private array &$captured) {}
            public function dispatch($event, $payload = [], $halt = false) { $this->captured[] = $event; return null; }
            public function listen($events, $listener = null, $priority = 0): void {}
            public function hasListeners($eventName): bool { return false; }
            public function subscribe($subscriber): void {}
            public function until($event, $payload = []) { return null; }
            public function push($event, $payload = []): void {}
            public function flush($event): void {}
            public function forget($event): void {}
            public function forgetPushed(): void {}
        };

        return new Ai($resolver, $events);
    }
}
