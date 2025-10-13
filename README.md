# Flarum AI

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/datlechin/flarum-ai.svg)](https://packagist.org/packages/datlechin/flarum-ai) [![Total Downloads](https://img.shields.io/packagist/dt/datlechin/flarum-ai.svg)](https://packagist.org/packages/datlechin/flarum-ai)

AI integration framework for Flarum with text generation, embeddings, and moderation. Multi-provider support (OpenAI, Gemini, Anthropic) with extensible architecture.

## Features

- 🤖 Text generation with streaming support
- 🔍 Vector embeddings for semantic search
- 🛡️ AI-powered content moderation
- 🔌 Multi-provider architecture (OpenAI, Gemini, Anthropic)
- ⚡ SSE streaming for real-time responses
- 🔧 Extensible provider system

## Installation

```bash
composer require datlechin/flarum-ai
```

## Configuration

1. Navigate to **Admin Panel → Extensions → AI**
2. Select your LLM provider (OpenAI, Gemini, or Anthropic)
3. Enter your API key
4. Configure model settings

## Developer Usage

### Text Generation

```php
use Datlechin\Ai\Providers\HttpProviderFactory;

// Get the provider instance
$provider = app(HttpProviderFactory::class)->createLlmProvider();

// Generate text
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'Hello!']
];

$result = $provider->complete($messages);
echo $result['content'];
```

### Streaming Text Generation

```php
// Stream responses in real-time
foreach ($provider->stream($messages) as $chunk) {
    echo $chunk; // Output each chunk as it arrives
}
```

### Embeddings

```php
use Datlechin\Ai\Providers\HttpProviderFactory;

// Get embeddings provider
$provider = app(HttpProviderFactory::class)->createEmbeddingsProvider();

// Generate embeddings
$text = "This is some text to embed";
$embedding = $provider->embed($text);

// Returns array of floats (vector representation)
print_r($embedding);
```

### Content Moderation

```php
use Datlechin\Ai\Providers\HttpProviderFactory;

// Get moderation provider
$provider = app(HttpProviderFactory::class)->createModerationProvider();

// Check content
$result = $provider->moderate("Content to check");

if ($result['flagged']) {
    // Handle flagged content
    print_r($result['categories']);
}
```

## Creating Custom Providers

Implement the provider interfaces:

```php
namespace MyExtension\Providers;

use Datlechin\Ai\Providers\Contracts\LlmProviderInterface;

class CustomLlmProvider implements LlmProviderInterface
{
    public function complete(array $messages, array $options = []): array
    {
        // Your implementation
        return [
            'content' => 'Generated text',
            'usage' => ['tokens' => 100]
        ];
    }

    public function stream(array $messages, array $options = []): \Generator
    {
        // Yield chunks
        yield "chunk1";
        yield "chunk2";
    }

    public function getName(): string
    {
        return 'custom';
    }

    public function getModel(): string
    {
        return 'custom-model';
    }
}
```

Register in `extend.php`:

```php
use Datlechin\Ai\Providers\ProviderCatalog;

return [
    (new Extend\ServiceProvider())
        ->register(function ($container) {
            $catalog = $container->make(ProviderCatalog::class);
            $catalog->register('custom', MyCustomProvider::class);
        }),
];
```

## Available Providers

### OpenAI

- Models: GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
- Supports: Text generation, embeddings, moderation
- Streaming: ✅

### Google Gemini

- Models: Gemini Pro, Gemini Flash
- Supports: Text generation, embeddings
- Streaming: ✅

### Anthropic

- Models: Claude 3.5 Sonnet, Claude 3.5 Haiku, Claude 3 Opus
- Supports: Text generation
- Streaming: ✅

## Events

Listen to AI events in your extensions:

```php
use Datlechin\Ai\Events\TextGenerated;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Event())
        ->listen(TextGenerated::class, function (TextGenerated $event) {
            // $event->content
            // $event->provider
            // $event->model
        }),
];
```

Available events:

- `TextGenerationStarted`
- `TextGenerated`
- `EmbeddingsStarted`
- `EmbeddingsGenerated`
- `ModerationStarted`
- `ModerationCompleted`
- `ProviderInitialized`
- `ProviderFailed`

## API Endpoints

### Generate Text

```http
POST /api/ai/generate
Content-Type: application/json

{
  "messages": [
    {"role": "system", "content": "You are helpful"},
    {"role": "user", "content": "Hello"}
  ],
  "stream": true
}
```

### Generate Embeddings

```http
POST /api/ai/embeddings
Content-Type: application/json

{
  "text": "Text to embed"
}
```

### Moderate Content

```http
POST /api/ai/moderate
Content-Type: application/json

{
  "content": "Content to check"
}
```

## Extension Examples

### Text Summarization

```php
$provider = app(HttpProviderFactory::class)->createLlmProvider();

$messages = [
    ['role' => 'system', 'content' => 'Summarize the following text concisely.'],
    ['role' => 'user', 'content' => $longText]
];

$summary = $provider->complete($messages);
```

### Semantic Search

```php
$embeddingsProvider = app(HttpProviderFactory::class)->createEmbeddingsProvider();

// Embed query
$queryVector = $embeddingsProvider->embed($searchQuery);

// Search in database (using vector similarity)
$results = DB::table('ai_embeddings')
    ->selectRaw('*, vector_distance(embedding, ?) as distance', [$queryVector])
    ->orderBy('distance')
    ->limit(10)
    ->get();
```

### Content Filtering

```php
$moderationProvider = app(HttpProviderFactory::class)->createModerationProvider();

$result = $moderationProvider->moderate($userContent);

if ($result['flagged']) {
    // Auto-hide or flag for review
    $post->hide();
}
```

## Configuration Options

Settings available in admin panel:

- `datlechin-ai.provider` - Selected provider (openai, gemini)
- `datlechin-ai.api_key` - API key for provider
- `datlechin-ai.models.selected.text` - Text generation model
- `datlechin-ai.models.selected.embeddings` - Embeddings model
- `datlechin-ai.models.selected.moderation` - Moderation model

Access in code:

```php
$provider = $settings->get('datlechin-ai.provider');
$model = $settings->get('datlechin-ai.models.selected.text');
```

## Requirements

- Flarum 1.2+
- PHP 8.1+
- Composer 2.0+
- Valid API key for chosen provider

## Links

- [Packagist](https://packagist.org/packages/datlechin/flarum-ai)
- [GitHub](https://github.com/datlechin/flarum-ai)

## Sponsor

If you find this extension helpful, you can support ongoing development through [GitHub Sponsors](https://github.com/sponsors/datlechin).
