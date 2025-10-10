<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Providers\Contracts;

interface EmbeddingsProviderInterface
{
    /**
     * Generate embeddings for given texts.
     *
     * @param array $texts Array of text strings
     * @param array $options Additional options
     * @return array Array of embedding vectors (each vector is array of floats)
     */
    public function embedTexts(array $texts, array $options = []): array;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the current embedding model being used.
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Get the dimension of the embedding vectors.
     *
     * @return int
     */
    public function getDimension(): int;
}
