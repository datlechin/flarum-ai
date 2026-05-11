<?php

namespace Datlechin\Ai\Provider;

use Datlechin\Ai\Provider\Contracts\Provider;
use InvalidArgumentException;

class ProviderRegistry
{
    /** @var array<string, Provider> */
    private array $providers = [];

    public function register(Provider $provider): void
    {
        $this->providers[$provider->manifest()->name] = $provider;
    }

    public function get(string $name): Provider
    {
        if (! isset($this->providers[$name])) {
            throw new InvalidArgumentException("AI provider [$name] is not registered.");
        }

        return $this->providers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    /**
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->providers);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function manifests(): array
    {
        $out = [];

        foreach ($this->providers as $name => $provider) {
            $out[$name] = $provider->manifest()->toArray();
        }

        return $out;
    }
}
