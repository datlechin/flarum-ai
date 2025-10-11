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
    private url;
    private body;
    private headers;
    private controller;
    private reader;
    private chunkCallback;
    private completeCallback;
    private errorCallback;
    private startCallback;
    private fullContent;
    private isStreaming;
    /**
     * Create a new AI stream handler.
     *
     * @param url - The API endpoint URL
     * @param body - The request body
     * @param headers - Optional additional headers
     */
    constructor(url: string, body: any, headers?: Record<string, string>);
    /**
     * Register a callback for each chunk of content received.
     */
    onChunk(callback: (content: string) => void): this;
    /**
     * Register a callback for when the stream completes successfully.
     */
    onComplete(callback: () => void): this;
    /**
     * Register a callback for when an error occurs.
     */
    onError(callback: (error: Error) => void): this;
    /**
     * Register a callback for when streaming starts.
     */
    onStart(callback: () => void): this;
    /**
     * Get the accumulated full content from all chunks.
     */
    getFullContent(): string;
    /**
     * Check if currently streaming.
     */
    isActive(): boolean;
    /**
     * Start streaming from the API.
     */
    start(): Promise<void>;
    /**
     * Cancel the stream.
     */
    cancel(): void;
    /**
     * Clean up resources.
     */
    private cleanup;
}
