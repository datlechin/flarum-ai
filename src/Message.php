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

final readonly class Message
{
    public function __construct(
        public Role $role,
        public string $text,
    ) {
    }

    public static function user(string $text): self
    {
        return new self(Role::User, $text);
    }

    public static function assistant(string $text): self
    {
        return new self(Role::Assistant, $text);
    }
}
