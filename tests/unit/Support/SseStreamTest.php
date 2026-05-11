<?php

namespace Datlechin\Ai\Tests\unit\Support;

use Datlechin\Ai\Support\SseStream;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class SseStreamTest extends TestCase
{
    public function test_yields_decoded_data_events(): void
    {
        $stream = Utils::streamFor(implode('', [
            "data: {\"chunk\":\"hello\"}\n\n",
            "data: {\"chunk\":\" world\"}\n\n",
            "data: [DONE]\n\n",
        ]));

        $events = iterator_to_array(SseStream::events($stream));

        $this->assertCount(2, $events);
        $this->assertSame('hello', $events[0]['chunk']);
        $this->assertSame(' world', $events[1]['chunk']);
    }

    public function test_stops_on_done_sentinel(): void
    {
        $stream = Utils::streamFor("data: {\"a\":1}\n\ndata: [DONE]\n\ndata: {\"b\":2}\n\n");

        $events = iterator_to_array(SseStream::events($stream));

        $this->assertCount(1, $events);
    }

    public function test_ignores_non_data_lines(): void
    {
        $stream = Utils::streamFor("event: ping\n: keepalive\ndata: {\"x\":1}\n\n");

        $events = iterator_to_array(SseStream::events($stream));

        $this->assertCount(1, $events);
        $this->assertSame(1, $events[0]['x']);
    }

    public function test_handles_payloads_split_across_buffer_boundaries(): void
    {
        $payload = 'data: {"value":"'.str_repeat('a', 8000)."\"}\n\n";
        $stream = Utils::streamFor($payload);

        $events = iterator_to_array(SseStream::events($stream));

        $this->assertCount(1, $events);
        $this->assertSame(8000, strlen($events[0]['value']));
    }
}
