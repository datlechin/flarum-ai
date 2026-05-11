<?php

namespace Datlechin\Ai\Tests\unit\Support;

use Datlechin\Ai\Support\ErrorScrubber;
use PHPUnit\Framework\TestCase;

class ErrorScrubberTest extends TestCase
{
    public function test_redacts_google_style_api_key_in_query_string(): void
    {
        $input = 'POST https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=AIzaSyA-real-secret-XYZ123';
        $output = ErrorScrubber::scrub($input);

        $this->assertStringNotContainsString('AIzaSyA-real-secret-XYZ123', $output);
        $this->assertStringContainsString('key=REDACTED', $output);
    }

    public function test_redacts_bearer_token_in_authorization_header(): void
    {
        $input = "Headers: Authorization: Bearer sk-proj-real-secret-token";
        $output = ErrorScrubber::scrub($input);

        $this->assertStringNotContainsString('sk-proj-real-secret-token', $output);
        $this->assertStringContainsString('Authorization: Bearer REDACTED', $output);
    }

    public function test_redacts_anthropic_style_x_api_key(): void
    {
        $input = 'x-api-key: sk-ant-real-key-here';
        $output = ErrorScrubber::scrub($input);

        $this->assertStringNotContainsString('sk-ant-real-key-here', $output);
    }

    public function test_leaves_non_secret_text_unchanged(): void
    {
        $input = 'Upstream returned 503 Service Unavailable.';

        $this->assertSame($input, ErrorScrubber::scrub($input));
    }
}
