<?php

namespace Datlechin\Ai\Provider\Google;

use Datlechin\Ai\Provider\Contracts\ModerationClient;
use Datlechin\Ai\Support\HttpTransport;

class GoogleModeration implements ModerationClient
{
    private const PROBABILITY_SCORE = [
        'NEGLIGIBLE' => 0.05,
        'LOW' => 0.25,
        'MEDIUM' => 0.6,
        'HIGH' => 0.9,
    ];

    public function __construct(
        private readonly HttpTransport $http,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function classify(string $text, array $options = []): array
    {
        $payload = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $text]]]],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
            ],
            'generationConfig' => ['maxOutputTokens' => 1],
        ];

        $response = $this->http->postJson(
            "/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
            $payload + $options,
        );

        $data = json_decode((string) $response->getBody(), true) ?: [];
        $ratings = $data['candidates'][0]['safetyRatings'] ?? [];

        $scores = [];
        $flagged = false;

        foreach ($ratings as $rating) {
            $category = strtolower(str_replace('HARM_CATEGORY_', '', $rating['category'] ?? ''));
            $probability = $rating['probability'] ?? 'NEGLIGIBLE';
            $score = self::PROBABILITY_SCORE[$probability] ?? 0.0;
            $scores[$category] = $score;

            if ($score >= 0.6) {
                $flagged = true;
            }
        }

        return ['flagged' => $flagged, 'scores' => $scores];
    }

    public function model(): string
    {
        return $this->model;
    }
}
