<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai;

final readonly class Prompt
{
    public function __construct(
        public string $instructions,
        public array $messages,
        public ?int $maxOutputTokens = null,
    ) {
    }

    public static function make(string $instructions, string $input, ?int $maxOutputTokens = null): self
    {
        return new self($instructions, [Message::user($input)], $maxOutputTokens);
    }
}
