<?php

namespace Datlechin\Ai\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class HttpTransport
{
    private Client $client;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $baseUrl, array $headers = [], int $timeout = 60)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => $headers + ['Content-Type' => 'application/json'],
            'timeout' => $timeout,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function postJson(string $path, array $payload): ResponseInterface
    {
        try {
            return $this->client->post($path, ['json' => $payload]);
        } catch (GuzzleException $e) {
            throw new RuntimeException(ErrorScrubber::message($e), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function postJsonStream(string $path, array $payload): ResponseInterface
    {
        try {
            return $this->client->post($path, ['json' => $payload, 'stream' => true]);
        } catch (GuzzleException $e) {
            throw new RuntimeException(ErrorScrubber::message($e), 0, $e);
        }
    }
}
