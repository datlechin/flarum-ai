<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\integration;

use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MigratesProviderSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('datlechin-ai');
    }

    #[Test]
    public function the_provider_its_key_and_its_text_model_carry_over(): void
    {
        $this->legacySettings([
            'datlechin-ai.provider' => 'anthropic',
            'datlechin-ai.anthropic.api_key' => 'sk-ant-test',
            'datlechin-ai.models.selected.text' => 'claude-sonnet-4-5',
            'datlechin-ai.models.selected.embeddings' => 'text-embedding-3-small',
        ]);

        $this->migrate();

        $this->assertSame([
            'datlechin-ai.anthropic.api_key' => 'sk-ant-test',
            'datlechin-ai.anthropic.model' => 'claude-sonnet-4-5',
            'datlechin-ai.driver' => 'anthropic',
        ], $this->aiSettings());
    }

    #[Test]
    public function a_custom_model_uses_the_name_that_was_typed(): void
    {
        $this->legacySettings([
            'datlechin-ai.provider' => 'gemini',
            'datlechin-ai.gemini.api_key' => 'AIza-test',
            'datlechin-ai.models.selected.text' => '__custom__',
            'datlechin-ai.models.custom.text' => 'gemini-3.8-flash',
        ]);

        $this->migrate();

        $this->assertSame([
            'datlechin-ai.driver' => 'gemini',
            'datlechin-ai.gemini.api_key' => 'AIza-test',
            'datlechin-ai.gemini.model' => 'gemini-3.8-flash',
        ], $this->aiSettings());
    }

    #[Test]
    public function openai_with_its_own_base_url_becomes_openai_compatible(): void
    {
        $this->legacySettings([
            'datlechin-ai.provider' => 'openai',
            'datlechin-ai.openai.api_key' => 'gsk-test',
            'datlechin-ai.openai.base_url' => 'https://api.groq.com/openai/v1',
            'datlechin-ai.models.selected.text' => '__custom__',
            'datlechin-ai.models.custom.text' => 'llama-4-scout',
        ]);

        $this->migrate();

        $this->assertSame([
            'datlechin-ai.driver' => 'openai-compatible',
            'datlechin-ai.openai-compatible.api_key' => 'gsk-test',
            'datlechin-ai.openai-compatible.base_url' => 'https://api.groq.com/openai/v1',
            'datlechin-ai.openai-compatible.model' => 'llama-4-scout',
            'datlechin-ai.openai.api_key' => 'gsk-test',
        ], $this->aiSettings());
    }

    #[Test]
    public function the_custom_base_url_of_a_custom_model_also_counts(): void
    {
        $this->legacySettings([
            'datlechin-ai.provider' => 'openai',
            'datlechin-ai.openai.api_key' => 'sk-or-test',
            'datlechin-ai.models.selected.text' => '__custom__',
            'datlechin-ai.models.custom.text' => 'deepseek/deepseek-v4',
            'datlechin-ai.models.custom_base_url.text' => 'https://openrouter.ai/api/v1',
        ]);

        $this->migrate();

        $settings = $this->aiSettings();

        $this->assertSame('openai-compatible', $settings['datlechin-ai.driver']);
        $this->assertSame('https://openrouter.ai/api/v1', $settings['datlechin-ai.openai-compatible.base_url']);
        $this->assertSame('deepseek/deepseek-v4', $settings['datlechin-ai.openai-compatible.model']);
    }

    #[Test]
    public function the_base_urls_of_the_other_providers_are_dropped(): void
    {
        $this->legacySettings([
            'datlechin-ai.provider' => 'gemini',
            'datlechin-ai.gemini.api_key' => 'AIza-test',
            'datlechin-ai.gemini.base_url' => 'https://proxy.example.com',
            'datlechin-ai.anthropic.base_url' => 'https://proxy.example.com',
        ]);

        $this->migrate();

        $this->assertSame([
            'datlechin-ai.driver' => 'gemini',
            'datlechin-ai.gemini.api_key' => 'AIza-test',
        ], $this->aiSettings());
    }

    #[Test]
    public function nothing_is_written_when_no_provider_was_chosen(): void
    {
        $this->legacySettings([
            'datlechin-ai.models.selected.text' => 'gpt-4o-mini',
        ]);

        $this->migrate();

        $this->assertSame([], $this->aiSettings());
    }

    private function legacySettings(array $settings): void
    {
        $this->database()->table('settings')->where('key', 'like', 'datlechin-ai.%')->delete();

        foreach ($settings as $key => $value) {
            $this->database()->table('settings')->insert(['key' => $key, 'value' => $value]);
        }
    }

    private function migrate(): void
    {
        $migration = require __DIR__.'/../../migrations/2026_10_27_000000_move_provider_settings_to_drivers.php';

        $migration['up']($this->database()->getSchemaBuilder());
    }

    private function aiSettings(): array
    {
        $settings = $this->database()->table('settings')->where('key', 'like', 'datlechin-ai.%')->pluck('value', 'key')->all();

        ksort($settings);

        return $settings;
    }
}
