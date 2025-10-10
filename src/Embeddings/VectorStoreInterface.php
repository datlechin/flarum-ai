<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Embeddings;

interface VectorStoreInterface
{
    /**
     * Upsert embeddings for a reference.
     *
     * @param string $refType Type of reference (discussion, post, user, etc.)
     * @param int $refId ID of the reference
     * @param array $embedding Vector embedding (array of floats)
     * @param string|null $content Original content for reference
     * @param array $meta Additional metadata
     * @return bool Success status
     */
    public function upsert(string $refType, int $refId, array $embedding, ?string $content = null, array $meta = []): bool;

    /**
     * Find similar items by vector similarity.
     *
     * @param array $queryEmbedding Query vector
     * @param string|null $refType Filter by reference type
     * @param int $topK Number of results to return
     * @return array Array of similar items with scores
     */
    public function findSimilar(array $queryEmbedding, ?string $refType = null, int $topK = 5): array;

    /**
     * Get embedding for a specific reference.
     *
     * @param string $refType
     * @param int $refId
     * @return array|null Embedding data or null if not found
     */
    public function get(string $refType, int $refId): ?array;

    /**
     * Delete embeddings for a reference.
     *
     * @param string $refType
     * @param int $refId
     * @return bool Success status
     */
    public function delete(string $refType, int $refId): bool;

    /**
     * Delete all embeddings for a reference type.
     *
     * @param string $refType
     * @return int Number of deleted items
     */
    public function deleteByType(string $refType): int;

    /**
     * Get total count of embeddings.
     *
     * @param string|null $refType Filter by type
     * @return int
     */
    public function count(?string $refType = null): int;
}
