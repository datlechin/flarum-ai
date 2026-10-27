# Changelog

All notable changes to this extension are listed here. It follows [Semantic Versioning](https://semver.org) and the [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format.

## [Unreleased]

## [1.0.0] - 2026-10-27

Rewritten for Flarum 2. Run `php flarum migrate` after updating; the provider, its key and its text model carry over from 0.1.x.

### Added

- An OpenAI-compatible provider with its own base URL, for OpenRouter, Groq, DeepSeek, Ollama, LM Studio and similar servers.
- The model list comes from the provider, and any other model ID can be typed in.
- **Send test prompt** on the AI page.
- Each provider keeps its own key and model.
- `Ai::generate()` and `Ai::stream()` for other extensions. A stream returns the full response, token usage included, when it ends.
- The `Generated` event after every generation.
- `Extend\Ai::driver()` to add a provider.
- Provider errors are written to the Flarum log.

### Changed

- **Breaking:** requires Flarum 2.0 and PHP 8.3.
- **Breaking:** the PHP API is `Datlechin\Ai\Ai` with `Prompt` and `Response`. `HttpProviderFactory`, the provider interfaces and the old events are gone.
- **Breaking:** the `datlechin-ai.provider` setting is now `datlechin-ai.driver`, and the model is stored per provider. The migration converts them.
- OpenAI uses the Responses API and asks OpenAI not to store responses.
- Anthropic uses API version `2026-10-21`.

### Removed

- **Breaking:** the `/api/ai/generate`, `/api/ai/embeddings`, `/api/ai/moderate` and `/api/ai/provider-catalog` endpoints. They let anyone with the permission send any prompt with the forum's API key.
- **Breaking:** embeddings, the file vector store and moderation. Nothing used them.
- The built-in model catalog, which went out of date with every model release.

### Fixed

- OpenAI requests went to `https://api.openai.com/chat/completions` instead of `/v1/chat/completions` and always failed.
- The API returned the provider's raw error message to whoever made the request.

## [0.1.1] - 2025-10-12

### Changed

- Lowered the Flarum requirement from 1.8 to 1.2.

## [0.1.0] - 2025-10-11

First release, for Flarum 1.

[Unreleased]: https://github.com/datlechin/flarum-ai/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/datlechin/flarum-ai/compare/v0.1.1...v1.0.0
[0.1.1]: https://github.com/datlechin/flarum-ai/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/datlechin/flarum-ai/releases/tag/v0.1.0
