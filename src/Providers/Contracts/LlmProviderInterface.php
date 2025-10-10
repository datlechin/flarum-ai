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

interface LlmProviderInterface
{
    /**
     * Generate a complete response from the LLM.
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return array Response with 'content', 'model', 'usage', etc.
     */
    public function complete(array $messages, array $options = []): array;

    /**
     * Generate a streaming response from the LLM.
     *
     * @param array $messages Array of message objects
     * @param array $options Additional options
     * @return \Generator Yields chunks of the response
     */
    public function stream(array $messages, array $options = []): \Generator;

    /**
     * Chat completion (alias for complete).
     *
     * @param array $messages
     * @param array $options
     * @return array
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the current model being used.
     *
     * @return string
     */
    public function getModel(): string;
}
