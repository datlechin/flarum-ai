<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Driver;

final readonly class Field
{
    public function __construct(
        public string $setting,
        public string $type,
        public string $label,
        public ?string $help = null,
        public array $rules = [],
        public ?string $placeholder = null,
    ) {
    }

    public static function apiKey(string $setting, string $help, bool $required = true): self
    {
        return new self(
            setting: $setting,
            type: 'password',
            label: 'datlechin-ai.admin.fields.api_key_label',
            help: $help,
            rules: [$required ? 'required' : 'nullable', 'string', 'not_regex:/\s/'],
        );
    }

    public function toArray(): array
    {
        return [
            'setting' => $this->setting,
            'type' => $this->type,
            'label' => $this->label,
            'help' => $this->help,
            'placeholder' => $this->placeholder,
            'required' => in_array('required', $this->rules, true),
        ];
    }
}
