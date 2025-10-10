<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->create('ai_moderation_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('content_type', 50); // 'post', 'discussion_title', 'user_bio', etc.
            $table->json('scores'); // { "toxicity": 0.8, "nsfw": 0.3, ... }
            $table->string('action', 50)->nullable(); // 'flag', 'hide', 'needs_approval', 'none'
            $table->text('reason')->nullable();
            $table->string('model', 100)->nullable();
            $table->timestamps();
        });
    },

    'down' => function (Builder $schema) {
        $schema->dropIfExists('ai_moderation_logs');
    }
];
