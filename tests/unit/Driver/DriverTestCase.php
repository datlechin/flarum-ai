<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\Ai\Tests\unit\Driver;

use Datlechin\Ai\Response as AiResponse;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

abstract class DriverTestCase extends TestCase
{
    protected array $history = [];

    protected function http(Response ...$responses): Client
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack]);
    }

    protected function json(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    protected function sse(string $body): Response
    {
        return new Response(200, ['Content-Type' => 'text/event-stream'], $body);
    }

    protected function sent(int $index = 0): RequestInterface
    {
        return $this->history[$index]['request'];
    }

    protected function payload(int $index = 0): array
    {
        return json_decode((string) $this->sent($index)->getBody(), true);
    }

    protected function drain(Generator $stream): array
    {
        $deltas = [];

        foreach ($stream as $delta) {
            $deltas[] = $delta;
        }

        return [$deltas, $stream->getReturn()];
    }

    protected function assertResponse(string $text, string $model, int $input, int $output, bool $truncated, AiResponse $response): void
    {
        $this->assertSame($text, $response->text);
        $this->assertSame($model, $response->model);
        $this->assertSame($input, $response->usage->inputTokens);
        $this->assertSame($output, $response->usage->outputTokens);
        $this->assertSame($truncated, $response->truncated);
    }
}
