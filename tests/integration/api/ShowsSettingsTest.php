<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\integration\api;

use Datlechin\Ai\Tests\integration\MocksProviderHttp;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ShowsSettingsTest extends TestCase
{
    use MocksProviderHttp;
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('datlechin-ai');

        $this->prepareDatabase([
            'users' => [$this->normalUser()],
        ]);
    }

    #[Test]
    public function only_admins_can_see_the_settings(): void
    {
        $response = $this->send($this->request('GET', '/api/datlechin-ai-settings', ['authenticatedAs' => 2]));

        $this->assertSame(403, $response->getStatusCode());
    }

    #[Test]
    public function lists_the_four_drivers_with_their_fields(): void
    {
        $attributes = $this->settings();

        $this->assertSame(['openai', 'anthropic', 'gemini', 'openai-compatible'], array_keys($attributes['drivers']));
        $this->assertSame('datlechin-ai.admin.drivers.openai', $attributes['drivers']['openai']['label']);
        $this->assertSame(
            [['setting' => 'datlechin-ai.openai.api_key', 'type' => 'password', 'label' => 'datlechin-ai.admin.fields.api_key_label', 'help' => 'datlechin-ai.admin.fields.openai_api_key_help', 'placeholder' => null, 'required' => true]],
            $attributes['drivers']['openai']['fields']
        );
        $this->assertSame(
            ['datlechin-ai.openai-compatible.base_url', 'datlechin-ai.openai-compatible.api_key'],
            array_column($attributes['drivers']['openai-compatible']['fields'], 'setting')
        );
        $this->assertFalse($attributes['drivers']['openai-compatible']['fields'][1]['required']);
    }

    #[Test]
    public function reports_the_missing_key_and_model_of_the_default_driver(): void
    {
        $attributes = $this->settings();

        $this->assertSame(['datlechin-ai.openai.api_key', 'datlechin-ai.openai.model'], array_keys($attributes['errors']));
        $this->assertSame(['ids' => [], 'error' => null], $attributes['models']);
    }

    #[Test]
    public function a_configured_driver_has_no_errors_and_lists_the_providers_models(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-test');
        $this->setting('datlechin-ai.openai.model', 'gpt-6-luna');

        $this->mockProvider($this->providerJson(['object' => 'list', 'data' => [
            ['id' => 'gpt-4o', 'created' => 1715367049],
            ['id' => 'gpt-6-luna', 'created' => 1788000000],
        ]]));

        $attributes = $this->settings();

        $this->assertSame([], $attributes['errors']);
        $this->assertSame(['ids' => ['gpt-6-luna', 'gpt-4o'], 'error' => null], $attributes['models']);
        $this->assertSame('Bearer sk-test', $this->providerRequests[0]['request']->getHeaderLine('Authorization'));
    }

    #[Test]
    public function the_model_list_is_fetched_once_and_cached(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-test');

        $this->mockProvider($this->providerJson(['data' => [['id' => 'gpt-6-luna', 'created' => 1]]]));

        $this->settings();
        $attributes = $this->settings();

        $this->assertSame(['gpt-6-luna'], $attributes['models']['ids']);
        $this->assertCount(1, $this->providerRequests);
    }

    #[Test]
    public function a_key_the_provider_rejects_shows_its_message_instead_of_models(): void
    {
        $this->setting('datlechin-ai.openai.api_key', 'sk-wrong');

        $this->mockProvider($this->providerJson(['error' => ['message' => 'Incorrect API key provided: sk-wrong.', 'type' => 'invalid_request_error', 'code' => 'invalid_api_key']], 401));

        $attributes = $this->settings();

        $this->assertSame(['ids' => [], 'error' => 'Incorrect API key provided: sk-wrong.'], $attributes['models']);
    }

    #[Test]
    public function the_errors_follow_the_chosen_driver(): void
    {
        $this->setting('datlechin-ai.driver', 'openai-compatible');
        $this->setting('datlechin-ai.openai-compatible.base_url', 'not a url');

        $attributes = $this->settings();

        $this->assertSame(['datlechin-ai.openai-compatible.base_url', 'datlechin-ai.openai-compatible.model'], array_keys($attributes['errors']));
    }

    #[Test]
    public function an_unknown_driver_is_reported(): void
    {
        $this->setting('datlechin-ai.driver', 'removed-extension');

        $attributes = $this->settings();

        $this->assertSame(['datlechin-ai.driver'], array_keys($attributes['errors']));
    }

    private function settings(): array
    {
        $response = $this->send($this->request('GET', '/api/datlechin-ai-settings', ['authenticatedAs' => 1]));

        $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());

        return json_decode((string) $response->getBody(), true)['data']['attributes'];
    }
}
