<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Embeddings\Adapters;

use Illuminate\Database\ConnectionInterface;
use Datlechin\Ai\Embeddings\VectorStoreInterface;

class FileJsonStore implements VectorStoreInterface
{
    private string $storagePath;
    private array $cache = [];
    private bool $loaded = false;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? storage_path('ai_embeddings.json');
    }

    public function upsert(string $refType, int $refId, array $embedding, ?string $content = null, array $meta = []): bool
    {
        $this->load();

        $key = $this->makeKey($refType, $refId);

        $this->cache[$key] = [
            'ref_type' => $refType,
            'ref_id' => $refId,
            'embedding' => $embedding,
            'content' => $content,
            'meta' => $meta,
            'updated_at' => time(),
        ];

        return $this->save();
    }

    public function findSimilar(array $queryEmbedding, ?string $refType = null, int $topK = 5): array
    {
        $this->load();

        $results = [];

        foreach ($this->cache as $key => $item) {
            if ($refType && $item['ref_type'] !== $refType) {
                continue;
            }

            $score = $this->cosineSimilarity($queryEmbedding, $item['embedding']);

            $results[] = [
                'ref_type' => $item['ref_type'],
                'ref_id' => $item['ref_id'],
                'score' => $score,
                'content' => $item['content'] ?? null,
                'meta' => $item['meta'] ?? [],
            ];
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, $topK);
    }

    public function get(string $refType, int $refId): ?array
    {
        $this->load();

        $key = $this->makeKey($refType, $refId);

        return $this->cache[$key] ?? null;
    }

    public function delete(string $refType, int $refId): bool
    {
        $this->load();

        $key = $this->makeKey($refType, $refId);

        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            return $this->save();
        }

        return false;
    }

    public function deleteByType(string $refType): int
    {
        $this->load();

        $count = 0;

        foreach ($this->cache as $key => $item) {
            if ($item['ref_type'] === $refType) {
                unset($this->cache[$key]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->save();
        }

        return $count;
    }

    public function count(?string $refType = null): int
    {
        $this->load();

        if ($refType === null) {
            return count($this->cache);
        }

        $count = 0;

        foreach ($this->cache as $item) {
            if ($item['ref_type'] === $refType) {
                $count++;
            }
        }

        return $count;
    }

    private function makeKey(string $refType, int $refId): string
    {
        return "{$refType}:{$refId}";
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        if (file_exists($this->storagePath)) {
            $data = file_get_contents($this->storagePath);
            $this->cache = json_decode($data, true) ?? [];
        }

        $this->loaded = true;
    }

    private function save(): bool
    {
        $dir = dirname($this->storagePath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($this->cache, JSON_PRETTY_PRINT);

        return file_put_contents($this->storagePath, $json) !== false;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }
}
