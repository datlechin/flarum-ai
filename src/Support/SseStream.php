<?php

namespace Datlechin\Ai\Support;

use Generator;
use Psr\Http\Message\StreamInterface;

class SseStream
{
    /**
     * @return Generator<int, array<string, mixed>>
     */
    public static function events(StreamInterface $body): Generator
    {
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(4096);

            while (($newline = strpos($buffer, "\n")) !== false) {
                $line = rtrim(substr($buffer, 0, $newline), "\r");
                $buffer = substr($buffer, $newline + 1);

                if ($line === '' || ! str_starts_with($line, 'data:')) {
                    continue;
                }

                $payload = ltrim(substr($line, 5));

                if ($payload === '[DONE]') {
                    return;
                }

                $decoded = json_decode($payload, true);

                if (is_array($decoded)) {
                    yield $decoded;
                }
            }
        }
    }
}
