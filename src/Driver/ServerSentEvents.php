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

use Generator;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class ServerSentEvents
{
    public static function parse(StreamInterface $body): Generator
    {
        $event = null;
        $data = [];

        while (! $body->eof()) {
            $line = rtrim(Utils::readLine($body), "\r\n");

            if ($line === '') {
                if ($data) {
                    yield [$event, implode("\n", $data)];
                }

                $event = null;
                $data = [];

                continue;
            }

            if (str_starts_with($line, ':')) {
                continue;
            }

            [$field, $value] = array_pad(explode(':', $line, 2), 2, '');

            if (str_starts_with($value, ' ')) {
                $value = substr($value, 1);
            }

            if ($field === 'event') {
                $event = $value;
            } elseif ($field === 'data') {
                $data[] = $value;
            }
        }

        if ($data) {
            yield [$event, implode("\n", $data)];
        }
    }
}
