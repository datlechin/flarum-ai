<?php

namespace Datlechin\Ai;

use Flarum\Foundation\AbstractServiceProvider;
use Illuminate\Contracts\Container\Container;
use Datlechin\Ai\Providers\HttpProviderFactory;
use Datlechin\Ai\Providers\ProviderCatalog;
use Datlechin\Ai\Settings\SettingsRepository;

class AIServiceProvider extends AbstractServiceProvider
{
    public function register() {}

    public function boot(Container $container)
    {
        $container->singleton(ProviderCatalog::class, function () {
            return new ProviderCatalog();
        });

        $container->singleton(SettingsRepository::class, function ($container) {
            return new SettingsRepository(
                $container->make(\Flarum\Settings\SettingsRepositoryInterface::class)
            );
        });

        $container->singleton(HttpProviderFactory::class, function ($container) {
            return new HttpProviderFactory(
                $container->make(SettingsRepository::class),
                $container->make(ProviderCatalog::class)
            );
        });
    }
}
