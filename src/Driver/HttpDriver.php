<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Driver;

use Datlechin\Ai\Exception\ProviderException;
use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

abstract class HttpDriver implements DriverInterface
{
    public function __construct(
        protected ClientInterface $http
    ) {
    }

    protected function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            return $this->http->request($method, $url, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();

            throw new ProviderException(
                $response ? $this->errorMessage($response) : $e->getMessage(),
                $response?->getStatusCode(),
                $e
            );
        } catch (GuzzleException $e) {
            throw new ProviderException($e->getMessage(), null, $e);
        }
    }

    protected function json(ResponseInterface $response): array
    {
        return $this->decode((string) $response->getBody());
    }

    protected function events(ResponseInterface $response): Generator
    {
        foreach (ServerSentEvents::parse($response->getBody()) as [$event, $data]) {
            yield $event => $data;
        }
    }

    protected function decode(string $json): array
    {
        try {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ProviderException('The provider sent a response that is not JSON.', null, $e);
        }

        return is_array($data) ? $data : [];
    }

    protected function errorMessage(ResponseInterface $response): string
    {
        $data = json_decode((string) $response->getBody(), true);

        // Gemini wraps errors on its streaming endpoint in a one-element array.
        if (is_array($data) && array_is_list($data)) {
            $data = $data[0] ?? [];
        }

        $message = match (true) {
            is_string($data['error']['message'] ?? null) => $data['error']['message'],
            is_string($data['error'] ?? null) => $data['error'],
            is_string($data['message'] ?? null) => $data['message'],
            default => null,
        };

        return $message ?: trim($response->getStatusCode().' '.$response->getReasonPhrase());
    }
}
