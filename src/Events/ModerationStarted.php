<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Events;

use Flarum\User\User;

class ModerationStarted
{
    public function __construct(
        public readonly User $actor,
        public string $text,
        public readonly string $provider,
        public readonly string $model
    ) {}
}
