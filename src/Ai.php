<?php

namespace Datlechin\Ai;

use Datlechin\Ai\Event\EmbeddingsCompleted;
use Datlechin\Ai\Event\ModerationCompleted;
use Datlechin\Ai\Event\ProviderFailed;
use Datlechin\Ai\Event\TextGenerationCompleted;
use Datlechin\Ai\Event\TextGenerationStarted;
use Flarum\User\User;
use Generator;
use Illuminate\Contracts\Events\Dispatcher;
use Throwable;

class Ai
{
    public function __construct(
        private readonly ModelResolver $resolver,
        private readonly Dispatcher $events,
    ) {}

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array{content: string, model: string, usage: array<string, mixed>, finish_reason: string|null}
     */
    public function generate(User $actor, array $messages, array $options = []): array
    {
        $provider = $this->resolver->provider();
        $model = $this->resolver->model('text');
        $llm = $provider->llm($model, $this->resolver->baseUrl());

        $started = new TextGenerationStarted($actor, $provider->manifest()->name, $model, count($messages), $options);
        $this->events->dispatch($started);

        try {
            $startedAt = microtime(true);
            $result = $llm->complete($messages, $started->options);

            $this->events->dispatch(new TextGenerationCompleted(
                actor: $actor,
                provider: $provider->manifest()->name,
                model: $model,
                messageCount: count($messages),
                outputChars: mb_strlen($result['content']),
                streamed: false,
                latencyMs: (int) ((microtime(true) - $startedAt) * 1000),
                usage: $result['usage'],
            ));

            return $result;
        } catch (Throwable $e) {
            $this->events->dispatch(ProviderFailed::from($actor, $provider->manifest()->name, 'text', $e));

            throw $e;
        }
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return Generator<int, string>
     */
    public function stream(User $actor, array $messages, array $options = []): Generator
    {
        $provider = $this->resolver->provider();
        $model = $this->resolver->model('text');
        $llm = $provider->llm($model, $this->resolver->baseUrl());

        $started = new TextGenerationStarted($actor, $provider->manifest()->name, $model, count($messages), $options);
        $this->events->dispatch($started);

        $startedAt = microtime(true);
        $outputChars = 0;

        try {
            foreach ($llm->stream($messages, $started->options) as $chunk) {
                $outputChars += mb_strlen($chunk);
                yield $chunk;
            }
        } catch (Throwable $e) {
            $this->events->dispatch(ProviderFailed::from($actor, $provider->manifest()->name, 'text', $e));

            throw $e;
        }

        $this->events->dispatch(new TextGenerationCompleted(
            actor: $actor,
            provider: $provider->manifest()->name,
            model: $model,
            messageCount: count($messages),
            outputChars: $outputChars,
            streamed: true,
            latencyMs: (int) ((microtime(true) - $startedAt) * 1000),
        ));
    }

    /**
     * @param array<int, string> $texts
     * @return array<int, array<int, float>>
     */
    public function embed(User $actor, array $texts): array
    {
        $provider = $this->resolver->provider();
        $model = $this->resolver->model('embeddings');
        $client = $provider->embeddings($model, $this->resolver->baseUrl());

        try {
            $startedAt = microtime(true);
            $vectors = $client->embed($texts);

            $this->events->dispatch(new EmbeddingsCompleted(
                actor: $actor,
                provider: $provider->manifest()->name,
                model: $model,
                inputCount: count($texts),
                dimension: $client->dimension(),
                latencyMs: (int) ((microtime(true) - $startedAt) * 1000),
            ));

            return $vectors;
        } catch (Throwable $e) {
            $this->events->dispatch(ProviderFailed::from($actor, $provider->manifest()->name, 'embeddings', $e));

            throw $e;
        }
    }

    /**
     * @return array{flagged: bool, scores: array<string, float>}
     */
    public function moderate(User $actor, string $text): array
    {
        $provider = $this->resolver->provider();
        $model = $this->resolver->model('moderation');
        $client = $provider->moderation($model, $this->resolver->baseUrl());

        try {
            $startedAt = microtime(true);
            $result = $client->classify($text);

            $this->events->dispatch(new ModerationCompleted(
                actor: $actor,
                provider: $provider->manifest()->name,
                model: $model,
                inputChars: mb_strlen($text),
                flagged: $result['flagged'],
                scores: $result['scores'],
                latencyMs: (int) ((microtime(true) - $startedAt) * 1000),
            ));

            return $result;
        } catch (Throwable $e) {
            $this->events->dispatch(ProviderFailed::from($actor, $provider->manifest()->name, 'moderation', $e));

            throw $e;
        }
    }

    public function resolver(): ModelResolver
    {
        return $this->resolver;
    }
}
