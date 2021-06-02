<?php

declare(strict_types=1);

namespace Survease;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Survease\Exceptions\AuthorizationException;
use Survease\Exceptions\ServiceUnavailableException;
use Survease\Exceptions\UnknownOperation;
use Survease\Exceptions\ValidationException;
use Survease\Operations;

/**
 * Class Client
 * @package Survease
 *
 * @method Operations\Survey\Resource survey(string $id)
 */
class Client
{
    private ClientInterface $client;

    private array $operations = [
        'survey' => Operations\Survey\Resource::class,
    ];

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     * @throws UnknownOperation
     */
    public function __call(string $name, array $arguments = [])
    {
        if (isset($this->operations[$name])) {
            return new $this->operations[$name]($this, ...$arguments);
        }

        throw new UnknownOperation(sprintf('No operation declared for `%s` name', $name));
    }

    /**
     * @throws AuthorizationException
     * @throws JsonException
     * @throws ServiceUnavailableException
     * @throws ValidationException
     * @throws ClientExceptionInterface
     */
    public function makeRequest(Operations\DispatchesRequests $resource): ApiResponse
    {
        try {
            $response = $this->client->sendRequest(
                new Request($resource->method(), $resource->uri(), [], $resource->payload())
            );
        } catch (ClientExceptionInterface $e) {
            if ($e instanceof RequestException) {
                $this->handleException($e);
            }

            throw $e;
        }

        return new ApiResponse($response);
    }

    /**
     * @throws AuthorizationException
     * @throws ServiceUnavailableException
     * @throws ValidationException
     * @throws JsonException
     */
    private function handleException(RequestException $e): void
    {
        if ($response = $e->getResponse()) {
            $isJson = in_array('application/json', $response->getHeader('Content-Type'), true);

            if ($isJson) {
                $decoded = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            }

            if (strpos((string) $response->getStatusCode(), '5') === 0) {
                throw new ServiceUnavailableException($response->getReasonPhrase(), $response->getStatusCode(), $e);
            } elseif ($response->getStatusCode() === 401) {
                throw new AuthorizationException($isJson ? $decoded['message'] : $response->getReasonPhrase(), 401, $e);
            } elseif ($response->getStatusCode() === 422 && $isJson) {
                throw new ValidationException($decoded['message'], $decoded['errors']);
            }
        }

        throw $e;
    }
}
