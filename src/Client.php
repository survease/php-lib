<?php

declare(strict_types=1);

namespace Survease;

class Client
{
    private const BASE_URL = 'https://app.survease.io/api';

    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = self::BASE_URL, string $version = 'v1')
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }
}
