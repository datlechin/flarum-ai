# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of AI Core for Flarum
- LLM provider abstraction layer with OpenAI and Gemini support
- Provider-specific model catalogs with dropdown selection
- "Custom (advanced)" mode for manual model specification
- Embeddings service with vector store interface
- File-based JSON vector store adapter (development)
- AI moderation service with configurable thresholds
- Text generation endpoint with streaming support
- Admin settings UI with model dropdowns per provider
- Provider catalog refresh mechanism
- Test buttons for all major capabilities
- Settings repository with typed getters
- HTTP provider factory for dependency injection
- API endpoints for generate, embed, moderate, summarize
- Migration files for embeddings and moderation logs
- Comprehensive documentation and usage examples
- Unit tests for provider catalog
- PostgreSQL pgvector migration schema (commented)
- Rate limiting configuration
- Permission system for AI operations
- **Comprehensive event system for extensibility**
  - `TextGenerationStarted` - Before text generation (mutable)
  - `TextGenerated` - After text generation completes
  - `EmbeddingsStarted` - Before embeddings generation (mutable)
  - `EmbeddingsGenerated` - After embeddings are generated
  - `ModerationStarted` - Before moderation analysis (mutable)
  - `ModerationCompleted` - After moderation finishes
  - `ProviderInitialized` - When AI provider is created
  - `ProviderFailed` - When provider operations fail
- Example event listeners for common use cases
  - Rate limiting implementation
  - Embedding caching
  - Automatic content moderation
  - Error logging and monitoring
- Comprehensive event documentation (`docs/EVENTS.md`)
- Event registration examples (`docs/extend.example.php`)
- **Server-Sent Events (SSE) streaming support**
  - `AIStreamHandler` utility class for handling SSE connections
  - Real-time chunk-by-chunk content delivery
  - `AIChatComponent` - Full-featured chat UI with streaming
  - Stream cancellation support
  - Automatic CSRF token handling
  - Streaming test in admin settings page
  - Comprehensive streaming documentation (`docs/STREAMING.md`)
  - CSS styling with animations and dark mode support
  - Memory-safe cleanup and error handling
- CSRF protection on all endpoints

### Planned
- Background job queue integration
- AI bot with rules engine
- Related discussions widget using embeddings
- Hybrid search (BM25 + vector similarity)
- Console commands for reindexing and testing
- Event system for extensibility
- Pgvector adapter implementation
- Moderation automation with post hiding
- Topic summarization service
- Composer helper suggestions
- Translation service stub
- Performance monitoring and telemetry

## [1.0.0] - TBD

### Initial Production Release
- Stable API contracts
- Full test coverage
- Production-ready pgvector support
- Comprehensive admin UI
- Rate limiting and retry logic
- Error handling and logging
- Security audit completed

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development guidelines.

## License

This project is licensed under the MIT License - see [LICENSE.md](LICENSE.md) for details.
