<?php

namespace Datlechin\Ai\Provider;

class ProviderManifest
{
    /**
     * @param array<int, array{value: string, label: string, dimension?: int|null}> $textModels
     * @param array<int, array{value: string, label: string, dimension?: int|null}> $embeddingModels
     * @param array<int, array{value: string, label: string}> $moderationModels
     * @param array<string, string> $defaults Map of capability => default model value.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly array $textModels = [],
        public readonly array $embeddingModels = [],
        public readonly array $moderationModels = [],
        public readonly array $defaults = [],
        public readonly ?string $defaultBaseUrl = null,
    ) {}

    public function supports(string $capability): bool
    {
        return ! empty($this->models($capability));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function models(string $capability): array
    {
        return match ($capability) {
            'text' => $this->textModels,
            'embeddings' => $this->embeddingModels,
            'moderation' => $this->moderationModels,
            default => [],
        };
    }

    public function defaultModel(string $capability): ?string
    {
        return $this->defaults[$capability] ?? null;
    }

    public function dimensionFor(string $embeddingModel): ?int
    {
        foreach ($this->embeddingModels as $model) {
            if (($model['value'] ?? null) === $embeddingModel) {
                return $model['dimension'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'capabilities' => array_values(array_filter(
                ['text', 'embeddings', 'moderation'],
                fn (string $cap) => $this->supports($cap),
            )),
            'models' => [
                'text' => $this->textModels,
                'embeddings' => $this->embeddingModels,
                'moderation' => $this->moderationModels,
            ],
            'defaults' => $this->defaults,
        ];
    }
}
