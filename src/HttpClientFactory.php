<?php

declare(strict_types=1);

namespace Survease;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class HttpClientFactory
{
    private const BASE_URI = 'https://app.survease.io/api';

    public static function make(
        string $apiKey,
        string $baseUri = self::BASE_URI,
        string $version = 'v1'
    ): ClientInterface {
        return new \GuzzleHttp\Client([
            'base_uri' => sprintf('%s/%s/', rtrim($baseUri, '/'), $version),
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
        ]);
    }
}
