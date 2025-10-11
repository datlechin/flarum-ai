/**
 * Utility class for handling Server-Sent Events (SSE) streaming from AI providers.
 *
 * Usage:
 * ```ts
 * const stream = new AIStreamHandler('/api/ai/generate', {
 *   messages: [{ role: 'user', content: 'Hello' }],
 *   stream: true
 * });
 *
 * stream.onChunk((content) => {
 *   console.log('Received chunk:', content);
 * });
 *
 * stream.onComplete(() => {
 *   console.log('Stream finished');
 * });
 *
 * stream.onError((error) => {
 *   console.error('Stream error:', error);
 * });
 *
 * stream.start();
 * ```
 */
export default class AIStreamHandler {
  private url: string;
  private body: any;
  private headers: Record<string, string>;
  private controller: AbortController | null = null;
  private reader: ReadableStreamDefaultReader<Uint8Array> | null = null;

  private chunkCallback: ((content: string) => void) | null = null;
  private completeCallback: (() => void) | null = null;
  private errorCallback: ((error: Error) => void) | null = null;
  private startCallback: (() => void) | null = null;

  private fullContent: string = '';
  private isStreaming: boolean = false;

  /**
   * Create a new AI stream handler.
   *
   * @param url - The API endpoint URL
   * @param body - The request body
   * @param headers - Optional additional headers
   */
  constructor(url: string, body: any, headers: Record<string, string> = {}) {
    this.url = url;
    this.body = { ...body, stream: true };
    this.headers = {
      'Content-Type': 'application/json',
      ...headers,
    };
  }

  /**
   * Register a callback for each chunk of content received.
   */
  onChunk(callback: (content: string) => void): this {
    this.chunkCallback = callback;
    return this;
  }

  /**
   * Register a callback for when the stream completes successfully.
   */
  onComplete(callback: () => void): this {
    this.completeCallback = callback;
    return this;
  }

  /**
   * Register a callback for when an error occurs.
   */
  onError(callback: (error: Error) => void): this {
    this.errorCallback = callback;
    return this;
  }

  /**
   * Register a callback for when streaming starts.
   */
  onStart(callback: () => void): this {
    this.startCallback = callback;
    return this;
  }

  /**
   * Get the accumulated full content from all chunks.
   */
  getFullContent(): string {
    return this.fullContent;
  }

  /**
   * Check if currently streaming.
   */
  isActive(): boolean {
    return this.isStreaming;
  }

  /**
   * Start streaming from the API.
   */
  async start(): Promise<void> {
    if (this.isStreaming) {
      throw new Error('Stream is already active');
    }

    this.controller = new AbortController();
    this.fullContent = '';
    this.isStreaming = true;

    try {
      // Get CSRF token from Flarum
      const csrfToken = (app as any).session?.csrfToken;
      if (csrfToken) {
        this.headers['X-CSRF-Token'] = csrfToken;
      }

      if (this.startCallback) {
        this.startCallback();
      }

      const response = await fetch(this.url, {
        method: 'POST',
        headers: this.headers,
        body: JSON.stringify(this.body),
        signal: this.controller.signal,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      if (!response.body) {
        throw new Error('Response body is null');
      }

      this.reader = response.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';

      console.log('[AIStreamHandler] Reader started, waiting for chunks...');

      while (true) {
        const { done, value } = await this.reader.read();

        if (done) {
          console.log('[AIStreamHandler] Stream ended (done=true)');
          break;
        }

        // Decode the chunk
        const chunk = decoder.decode(value, { stream: true });
        console.log('[AIStreamHandler] Raw chunk received:', chunk.length, 'bytes');

        // Add to buffer and process complete lines
        buffer += chunk;
        const lines = buffer.split('\n');

        // Keep the last incomplete line in buffer
        buffer = lines.pop() || '';

        for (const line of lines) {
          if (line.startsWith('data: ')) {
            const data = line.slice(6).trim();

            if (data === '[DONE]') {
              console.log('[AIStreamHandler] Received [DONE] signal');
              if (this.completeCallback) {
                this.completeCallback();
              }
              break;
            }

            if (data) {
              try {
                const parsed = JSON.parse(data);
                if (parsed.content) {
                  console.log('[AIStreamHandler] Parsed content chunk:', parsed.content.substring(0, 50) + '...');
                  this.fullContent += parsed.content;
                  if (this.chunkCallback) {
                    this.chunkCallback(parsed.content);
                  }
                }
              } catch (e) {
                console.warn('[AIStreamHandler] Failed to parse SSE data:', data, e);
              }
            }
          }
        }
      }

      // Process any remaining data in buffer
      if (buffer.trim()) {
        console.log('[AIStreamHandler] Processing remaining buffer:', buffer);
      }
    } catch (error: any) {
      if (error.name === 'AbortError') {
        // Stream was cancelled, not an error
        return;
      }

      if (this.errorCallback) {
        this.errorCallback(error);
      } else {
        throw error;
      }
    } finally {
      this.cleanup();
    }
  }

  /**
   * Cancel the stream.
   */
  cancel(): void {
    if (this.controller) {
      this.controller.abort();
    }
    this.cleanup();
  }

  /**
   * Clean up resources.
   */
  private cleanup(): void {
    this.isStreaming = false;
    this.reader = null;
    this.controller = null;
  }
}
