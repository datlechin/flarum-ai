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

interface ModerationProviderInterface
{
    /**
     * Classify text for moderation concerns.
     *
     * @param string $text
     * @param array $options
     * @return array Scores keyed by category (toxicity, nsfw, hate, etc.)
     */
    public function classifyText(string $text, array $options = []): array;

    /**
     * Classify image for moderation concerns (optional).
     *
     * @param string $imageUrl
     * @param array $options
     * @return array Scores keyed by category
     */
    public function classifyImage(string $imageUrl, array $options = []): array;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the current moderation model being used.
     *
     * @return string
     */
    public function getModel(): string;
}
