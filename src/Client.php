<?php

declare(strict_types=1);

namespace Survease\Api;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use GuzzleHttp\ClientInterface;
use Survease\Api\Exceptions\AuthorizationException;
use Survease\Api\Exceptions\ServiceUnavailableException;
use Survease\Api\Exceptions\UnknownOperation;
use Survease\Api\Exceptions\ValidationException;
use Survease\Api\Operations;

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
            return new $this->operations[$name](...$arguments);
        }

        throw new UnknownOperation(sprintf('No operation declared for `%s` name', $name));
    }

    /**
     * @param Operations\DispatchableResource $resource
     * @param array $options Additional request options passed to http client
     * @return ApiResponse
     * @throws AuthorizationException
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws ServiceUnavailableException
     * @throws ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeRequest(Operations\DispatchableResource $resource, array $options = []): ApiResponse
    {
        try {
            $response = $this->client->send(
                new Request($resource->method(), $resource->uri(), [], $resource->payload()),
                $options
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
     * @param Operations\DispatchableResource $resource
     * @param array $options Additional request options passed to http client
     * @return PromiseInterface
     */
    public function makeRequestAsync(Operations\DispatchableResource $resource, array $options = []): PromiseInterface
    {
        return $this->client->sendAsync(
            new Request($resource->method(), $resource->uri(), [], $resource->payload()),
            $options
        );
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
