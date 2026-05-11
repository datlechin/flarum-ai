<?php

namespace Datlechin\Ai\Support;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Throwable;

class ErrorScrubber
{
    public static function message(Throwable $e): string
    {
        if ($e instanceof RequestException && $e->hasResponse()) {
            $status = $e->getResponse()->getStatusCode();
            $reason = $e->getResponse()->getReasonPhrase();
            $body = (string) $e->getResponse()->getBody();

            return sprintf('Upstream %d %s: %s', $status, $reason, self::scrub(self::truncate($body, 500)));
        }

        if ($e instanceof GuzzleException) {
            return 'Upstream request failed: '.self::scrub($e->getMessage());
        }

        return self::scrub($e->getMessage());
    }

    public static function scrub(string $value): string
    {
        $value = preg_replace('/([?&])key=[^&\s"\']+/i', '$1key=REDACTED', $value) ?? $value;
        $value = preg_replace('/(Authorization:\s*Bearer\s+)[A-Za-z0-9._\-]+/i', '$1REDACTED', $value) ?? $value;
        $value = preg_replace('/(x-api-key[":\s]+)[A-Za-z0-9._\-]+/i', '$1REDACTED', $value) ?? $value;

        return $value;
    }

    private static function truncate(string $value, int $max): string
    {
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max).'…' : $value;
    }
}
