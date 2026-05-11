# Flarum AI

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/datlechin/flarum-ai.svg)](https://packagist.org/packages/datlechin/flarum-ai) [![Total Downloads](https://img.shields.io/packagist/dt/datlechin/flarum-ai.svg)](https://packagist.org/packages/datlechin/flarum-ai)

AI integration library for Flarum 2.x. Text generation, embeddings, and moderation through a single self-declaring provider registry. Ships with OpenAI, Anthropic, and Google as built-in providers. Built as a library other extensions consume, not as a standalone forum feature.

## Installation

```bash
composer require datlechin/flarum-ai
```

## Configuration

1. Open the **Admin Panel**, go to **Extensions**, and enable **AI**.
2. Pick a provider, drop in the API key, and choose a model. Optional: set a base URL when routing through a proxy or self-hosted gateway.
3. Adjust the maximum input length if your community needs long-form prompts.

## Using the API in another extension

Inject the `Ai` façade. It dispatches lifecycle events automatically so usage tracking, rate limiting, and audit logging can be implemented as listeners.

```php
use Datlechin\Ai\Ai;
use Flarum\User\User;

class MyService
{
    public function __construct(private Ai $ai) {}

    public function summarise(User $actor, string $text): string
    {
        return $this->ai->generate($actor, [
            ['role' => 'system', 'content' => 'Summarise the input in one sentence.'],
            ['role' => 'user', 'content' => $text],
        ])['content'];
    }
}
```

Streaming returns a generator:

```php
foreach ($this->ai->stream($actor, $messages) as $chunk) {
    echo $chunk;
}
```

Embeddings and moderation follow the same shape:

```php
$vectors = $this->ai->embed($actor, ['hello world', 'second text']);
$result  = $this->ai->moderate($actor, 'some user content');
```

For direct access to the underlying client (custom retry policy, vendor-specific options), reach through `ModelResolver`:

```php
$llm = $this->ai->resolver()->provider()->llm(
    $this->ai->resolver()->model('text'),
    $this->ai->resolver()->baseUrl(),
);
```

## Registering a custom provider

Extend the registry from your own extension's service provider:

```php
use Datlechin\Ai\Provider\ProviderRegistry;
use Flarum\Extend;

return [
    (new Extend\ServiceProvider())
        ->register(function ($container) {
            $container->extend(ProviderRegistry::class, function (ProviderRegistry $registry) {
                $registry->register(new \YourVendor\YourProvider(/* config */));
                return $registry;
            });
        }),
];
```

A provider is any class implementing `Datlechin\Ai\Provider\Contracts\Provider`. It exposes a `ProviderManifest` (name, label, models, defaults, dimensions) and returns per-capability clients through `llm()`, `embeddings()`, and `moderation()`. Capabilities the provider does not support throw `BadMethodCallException`; consumers gate on `manifest()->supports($capability)` before calling.

## Built-in providers

| Provider | Text | Embeddings | Moderation |
| -------- | ---- | ---------- | ---------- |
| OpenAI | GPT-5 family, GPT-4o | text-embedding-3 (small/large) | omni-moderation-latest |
| Anthropic | Claude 4 family, Claude Sonnet 3.7 | -- | -- |
| Google | Gemini 2.5 Pro / Flash / Flash-Lite | text-embedding-004, gemini-embedding-001 | safety ratings via Flash-Lite |

## Events

Payloads carry counts and timings only. Raw user content is never included, and failure messages are scrubbed of API keys.

- `Datlechin\Ai\Event\TextGenerationStarted` (mutable options)
- `Datlechin\Ai\Event\TextGenerationCompleted`
- `Datlechin\Ai\Event\EmbeddingsCompleted`
- `Datlechin\Ai\Event\ModerationCompleted`
- `Datlechin\Ai\Event\ProviderFailed`

## Requirements

- Flarum 2.0
- PHP 8.2 or newer
- A valid API key for the provider you choose

## Links

- [Packagist](https://packagist.org/packages/datlechin/flarum-ai)
- [GitHub](https://github.com/datlechin/flarum-ai)
- [Sponsor on GitHub](https://github.com/sponsors/datlechin)
