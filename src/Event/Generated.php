<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Event;

use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;

class Generated
{
    public function __construct(
        public readonly string $driver,
        public readonly Prompt $prompt,
        public readonly Response $response,
    ) {
    }
}
