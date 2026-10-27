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

final readonly class Response
{
    public function __construct(
        public string $text,
        public string $model,
        public Usage $usage = new Usage(),
        public bool $truncated = false,
    ) {
    }
}
