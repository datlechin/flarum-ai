<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

return [
    'openai' => [
        'text' => [
            ['value' => 'gpt-5', 'label' => 'GPT-5', 'capability' => ['text']],
            ['value' => 'gpt-5-mini', 'label' => 'GPT-5 mini', 'capability' => ['text']],
            ['value' => 'gpt-5-nano', 'label' => 'GPT-5 nano', 'capability' => ['text'], 'is_default' => true],
            ['value' => 'gpt-5-pro', 'label' => 'GPT-5 pro', 'capability' => ['text'], 'is_default' => true],
            ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['text']],
        ],
        // 'embeddings' => [
        //     ['value' => 'text-embedding-3-large', 'label' => 'Text Embedding 3 Large', 'capability' => ['embeddings']],
        //     ['value' => 'text-embedding-3-small', 'label' => 'Text Embedding 3 Small', 'capability' => ['embeddings'], 'is_default' => true],
        //     ['value' => 'text-embedding-ada-002', 'label' => 'Text Embedding Ada 002', 'capability' => ['embeddings'], 'is_default' => true],
        //     ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['embeddings']],
        // ],
        // 'moderation' => [
        //     ['value' => 'omn-moderation-latest', 'label' => 'Omn Moderation Latest', 'capability' => ['moderation'], 'is_default' => true],
        //     ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['moderation']],
        // ],
    ],

    'gemini' => [
        'text' => [
            ['value' => 'gemini-2.5-pro', 'label' => 'Gemini 2.5 Pro', 'capability' => ['text']],
            ['value' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash', 'capability' => ['text'], 'is_default' => true],
            ['value' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash-Lite', 'capability' => ['text']],
            ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['text']],
        ],
        // 'embeddings' => [
        //     ['value' => 'text-embedding-004', 'label' => 'Text Embedding 004', 'capability' => ['embeddings'], 'is_default' => true],
        //     ['value' => 'gemini-embedding-001', 'label' => 'Gemini Embedding 001', 'capability' => ['embeddings']],
        //     ['value' => 'gemini-embedding-exp', 'label' => 'Gemini Embedding Experimental', 'capability' => ['embeddings']],
        //     ['value' => 'embedding-001', 'label' => 'Embedding 001', 'capability' => ['embeddings']],
        //     ['value' => 'embedding-gecko-001', 'label' => 'Embedding Gecko 001', 'capability' => ['embeddings']],
        //     ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['embeddings']],
        // ],
    ],

    'anthropic' => [
        'text' => [
            ['value' => 'claude-sonnet-4-5', 'label' => 'Claude Sonnet 4.5', 'capability' => ['text'], 'is_default' => true],
            ['value' => 'claude-sonnet-4-0', 'label' => 'Claude Sonnet 4', 'capability' => ['text']],
            ['value' => 'claude-3-7-sonnet-latest', 'label' => 'Claude Sonnet 3.7', 'capability' => ['text']],
            ['value' => 'claude-opus-4-1', 'label' => 'Claude Opus 4.1', 'capability' => ['text']],
            ['value' => 'claude-opus-4-0', 'label' => 'Claude Opus 4', 'capability' => ['text']],
            ['value' => 'claude-3-5-haiku-latest', 'label' => 'Claude Haiku 3.5', 'capability' => ['text']],
            ['value' => '__custom__', 'label' => 'Custom (advanced)', 'capability' => ['text']],
        ],
    ],
];
