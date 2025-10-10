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
        $schema->create('ai_embeddings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_type', 100)->index(); // 'discussion', 'post', 'user', etc.
            $table->unsignedInteger('ref_id')->index();
            $table->text('content')->nullable(); // Original text for reference
            $table->string('model', 100)->nullable();
            $table->json('meta')->nullable(); // Additional metadata
            $table->timestamps();

            // For pgvector: Use raw SQL to add vector column
            // $table->rawColumn('embedding', 'vector(1536)');
            // For MySQL/MariaDB fallback: Store as JSON array
            $table->json('embedding')->nullable();

            $table->unique(['ref_type', 'ref_id']);
        });

        // If using PostgreSQL with pgvector extension
        // Uncomment the following to create a vector index
        // DB::statement('CREATE INDEX ai_embeddings_embedding_idx ON ai_embeddings USING ivfflat (embedding vector_cosine_ops)');
    },

    'down' => function (Builder $schema) {
        $schema->dropIfExists('ai_embeddings');
    }
];
