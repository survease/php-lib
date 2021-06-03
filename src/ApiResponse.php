<?php

declare(strict_types=1);

namespace Survease\Api;

use JsonException;
use Psr\Http\Message\ResponseInterface;

class ApiResponse
{
    private ResponseInterface $response;

    private ?array $responseJson = null;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function isSuccessful(): bool
    {
        return $this->response->getStatusCode() === 200;
    }

    /**
     * @throws JsonException
     */
    public function getPayload(): array
    {
        if (null === $this->responseJson) {
            if ($this->isJson()) {
                $this->responseJson = json_decode($this->response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            } else {
                return $this->responseJson = [$this->response->getBody()->getContents()];
            }
        }

        return $this->responseJson;
    }

    private function isJson(): bool
    {
        return in_array('application/json', $this->response->getHeader('Content-Type'), true);
    }
}
