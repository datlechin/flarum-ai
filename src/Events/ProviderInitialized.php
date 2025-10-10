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

class ProviderInitialized
{
    public function __construct(
        public readonly ?User $actor,
        public readonly string $providerType,
        public readonly string $providerName,
        public readonly string $model,
        public readonly ?string $baseUrl = null
    ) {}
}
