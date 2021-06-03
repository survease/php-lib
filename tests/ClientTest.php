<?php

declare(strict_types=1);

namespace Survease\Tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Survease\Api\Client;
use Survease\Api\Exceptions\AuthorizationException;
use Survease\Api\Exceptions\ServiceUnavailableException;
use Survease\Api\HttpClientFactory;
use Survease\Api\Operations\DispatchableResource;

class ClientTest extends TestCase
{
    public function testClientInitiation(): void
    {
        $httpClient = HttpClientFactory::make('apikey');

        $client = new Client($httpClient);

        static::assertInstanceOf(Client::class, $client);
    }

    public function testServerException(): void
    {
        $mockedHttpClient = new \GuzzleHttp\Client([
            'handler' => HandlerStack::create(
                new MockHandler([
                    new ServerException('Service Unavailable', new Request('POST', '/'), new Response(500)),
                ])
            ),
        ]);

        $client = new Client($mockedHttpClient);

        $c = new class() implements DispatchableResource {
            public function uri(): string
            {
                return '/uri';
            }

            public function method(): string
            {
                return 'post';
            }

            public function payload(): ?string
            {
                return null;
            }
        };

        $this->expectException(ServiceUnavailableException::class);

        $client->makeRequest($c);
    }

    public function testMissingAuthorizationHeader(): void
    {
        $mockedHttpClient = new \GuzzleHttp\Client([
            'handler' => HandlerStack::create(
                new MockHandler([
                    new ClientException('Auth header missing', new Request('POST', '/'), new Response(401)),
                ])
            ),
        ]);

        $client = new Client($mockedHttpClient);

        $c = new class() implements DispatchableResource {
            public function uri(): string
            {
                return '/uri';
            }

            public function method(): string
            {
                return 'post';
            }

            public function payload(): ?string
            {
                return null;
            }
        };

        $this->expectException(AuthorizationException::class);

        $client->makeRequest($c);
    }

    public function testAsyncRequest()
    {
        $mockedHttpClient = new \GuzzleHttp\Client([
            'handler' => HandlerStack::create(
                new MockHandler([
                    new Response(200, [], json_encode(['message' => 'Msg', 'errors' => []])),
                ])
            ),
        ]);

        $client = new Client($mockedHttpClient);

        $c = new class() implements DispatchableResource {
            public function uri(): string
            {
                return '/uri';
            }

            public function method(): string
            {
                return 'post';
            }

            public function payload(): ?string
            {
                return null;
            }
        };

        $client->makeRequestAsync($c)
            ->then(function (ResponseInterface $response) {
                static::assertSame(200, $response->getStatusCode());
            }, function () {
                static::assertFalse(true); // shouldn't be triggered
            })->wait(); // wait, otherwise phpunit marks this test as risky
    }
}
