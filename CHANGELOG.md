# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Self-declaring `ProviderRegistry`. Each provider returns a `ProviderManifest` (name, label, models per capability, defaults, dimensions) and is looked up by name. Custom providers register through `$container->extend(ProviderRegistry::class, ...)` from any other extension.
- Per-capability client interfaces: `LlmClient`, `EmbeddingsClient`, `ModerationClient`. Vendors expose them through `Provider::llm()`, `embeddings()`, `moderation()`. Capabilities the vendor does not support throw `BadMethodCallException`; consumers gate on `manifest()->supports()`.
- `Ai` façade with `generate()`, `stream()`, `embed()`, `moderate()`. Dispatches lifecycle events around every call.
- Lifecycle events: `TextGenerationStarted` (mutable options), `TextGenerationCompleted`, `EmbeddingsCompleted`, `ModerationCompleted`, `ProviderFailed`. None carry raw user content.
- `ErrorScrubber` redacts API keys, bearer tokens, and `x-api-key` values from error surfaces.
- `SseStream` parses upstream server-sent-events streams with buffered reads.
- Built-in providers: OpenAI (text, embeddings, moderation), Anthropic (text), Google Gemini (text, embeddings, moderation via safety ratings).
- Declarative admin settings page via `Extend.Admin().setting()` and `.customSetting()`, with a model picker that adapts to the active provider's manifest.
- Unit tests for `ProviderManifest`, `ProviderRegistry`, `ErrorScrubber`, and `SseStream`. Backend CI enabled.
