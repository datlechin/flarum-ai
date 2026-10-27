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

use Datlechin\Ai\Driver\ServerSentEvents;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ServerSentEventsTest extends TestCase
{
    #[Test]
    public function reads_the_event_name_and_data_of_each_event(): void
    {
        $events = $this->parse("event: ping\ndata: {\"a\":1}\n\nevent: done\ndata: {\"b\":2}\n\n");

        $this->assertSame([['ping', '{"a":1}'], ['done', '{"b":2}']], $events);
    }

    #[Test]
    public function an_event_without_a_name_has_a_null_name(): void
    {
        $this->assertSame([[null, '[DONE]']], $this->parse("data: [DONE]\n\n"));
    }

    #[Test]
    public function joins_multi_line_data_with_newlines(): void
    {
        $this->assertSame([[null, "first\nsecond"]], $this->parse("data: first\ndata: second\n\n"));
    }

    #[Test]
    public function ignores_comment_lines(): void
    {
        $this->assertSame([[null, 'x']], $this->parse(": OPENROUTER PROCESSING\n\n: keep-alive\ndata: x\n\n"));
    }

    #[Test]
    public function understands_crlf_line_endings(): void
    {
        $this->assertSame([['a', '1'], [null, '2']], $this->parse("event: a\r\ndata: 1\r\n\r\ndata: 2\r\n\r\n"));
    }

    #[Test]
    public function keeps_a_final_event_that_has_no_trailing_blank_line(): void
    {
        $this->assertSame([[null, '1'], [null, '2']], $this->parse("data: 1\n\ndata: 2"));
    }

    #[Test]
    public function strips_only_one_leading_space_from_a_value(): void
    {
        $this->assertSame([[null, '  indented'], [null, 'tight']], $this->parse("data:   indented\n\ndata:tight\n\n"));
    }

    #[Test]
    public function skips_blank_lines_between_events(): void
    {
        $this->assertSame([[null, '1']], $this->parse("\n\ndata: 1\n\n\n"));
    }

    private function parse(string $body): array
    {
        return iterator_to_array(ServerSentEvents::parse(Utils::streamFor($body)), false);
    }
}
