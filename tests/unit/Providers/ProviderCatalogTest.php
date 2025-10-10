<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\Unit\Providers;

use Datlechin\Ai\Providers\ProviderCatalog;
use PHPUnit\Framework\TestCase;

class ProviderCatalogTest extends TestCase
{
    private ProviderCatalog $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalog = new ProviderCatalog();
    }

    public function testGetModelsReturnsCorrectStructure()
    {
        $models = $this->catalog->getModels('openai', 'text');
        
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        
        foreach ($models as $model) {
            $this->assertArrayHasKey('value', $model);
            $this->assertArrayHasKey('label', $model);
            $this->assertArrayHasKey('capability', $model);
        }
    }

    public function testGetDefaultModelReturnsCorrectValue()
    {
        $defaultModel = $this->catalog->getDefaultModel('openai', 'text');
        
        $this->assertNotNull($defaultModel);
        $this->assertEquals('gpt-4o-mini', $defaultModel);
    }

    public function testGetDefaultModelForGemini()
    {
        $defaultModel = $this->catalog->getDefaultModel('gemini', 'text');
        
        $this->assertNotNull($defaultModel);
        $this->assertEquals('gemini-1.5-flash', $defaultModel);
    }

    public function testIsValidModelReturnsTrueForValidModel()
    {
        $isValid = $this->catalog->isValidModel('openai', 'text', 'gpt-4o-mini');
        
        $this->assertTrue($isValid);
    }

    public function testIsValidModelReturnsFalseForInvalidModel()
    {
        $isValid = $this->catalog->isValidModel('openai', 'text', 'invalid-model');
        
        $this->assertFalse($isValid);
    }

    public function testIsCustomModelReturnsTrueForCustomValue()
    {
        $isCustom = $this->catalog->isCustomModel('__custom__');
        
        $this->assertTrue($isCustom);
    }

    public function testIsCustomModelReturnsFalseForRegularModel()
    {
        $isCustom = $this->catalog->isCustomModel('gpt-4o-mini');
        
        $this->assertFalse($isCustom);
    }

    public function testGetAllReturnsCompleteStructure()
    {
        $all = $this->catalog->getAll();
        
        $this->assertArrayHasKey('openai', $all);
        $this->assertArrayHasKey('gemini', $all);
        
        $this->assertArrayHasKey('text', $all['openai']);
        $this->assertArrayHasKey('embeddings', $all['openai']);
        $this->assertArrayHasKey('moderation', $all['openai']);
    }

    public function testReloadRefreshesCache()
    {
        $before = $this->catalog->getAll();
        $this->catalog->reload();
        $after = $this->catalog->getAll();
        
        $this->assertEquals($before, $after);
    }
}
