<?php

namespace Datlechin\Ai;

use Datlechin\Ai\Provider\Anthropic\AnthropicProvider;
use Datlechin\Ai\Provider\Google\GoogleProvider;
use Datlechin\Ai\Provider\OpenAi\OpenAiProvider;
use Datlechin\Ai\Provider\ProviderRegistry;
use Flarum\Foundation\AbstractServiceProvider;

class AiServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Settings::class);
        $this->container->singleton(ModelResolver::class);
        $this->container->singleton(Ai::class);

        $this->container->singleton(ProviderRegistry::class, function ($container) {
            $registry = new ProviderRegistry();
            $settings = $container->make(Settings::class);

            $registry->register(new OpenAiProvider($settings->apiKey('openai')));
            $registry->register(new AnthropicProvider($settings->apiKey('anthropic')));
            $registry->register(new GoogleProvider($settings->apiKey('google')));

            return $registry;
        });
    }
}
