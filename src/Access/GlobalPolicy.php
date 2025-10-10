<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class GlobalPolicy extends AbstractPolicy
{
    public function generate(User $actor): ?string
    {
        if ($actor->hasPermission('datlechin-ai.generate')) {
            return $this->allow();
        }

        return null;
    }

    public function embed(User $actor): ?string
    {
        if ($actor->hasPermission('datlechin-ai.embed')) {
            return $this->allow();
        }

        return null;
    }

    public function moderate(User $actor): ?string
    {
        if ($actor->hasPermission('datlechin-ai.moderate')) {
            return $this->allow();
        }

        return null;
    }

    public function viewCatalog(User $actor): ?string
    {
        if (! $actor->isGuest()) {
            return $this->allow();
        }

        return null;
    }
}
