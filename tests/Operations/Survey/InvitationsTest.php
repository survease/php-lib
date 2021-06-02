<?php

declare(strict_types=1);

namespace Survease\Tests\Operations\Survey;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\RequestOptions;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Survease\Client;
use Survease\DTO\InvitationRecipient;
use Survease\Exceptions\ValidationException;
use Survease\Tests\ServerRequestValidatorFactory;

class InvitationsTest extends TestCase
{
    public function testAddSingleInvitation(): void
    {
        $client = $this->makeClient(
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'message' => 'All good',
                    'errors' => [],
                ])
            )
        );

        $response = $client->survey('id')
            ->invitations()
            ->add(new InvitationRecipient('unit@test.com', 'Unit', 'Test', 'en', time() - 1000, time()))
            ->dispatch();

        static::assertEmpty($response->getPayload()['errors']);
        static::assertIsString($response->getPayload()['message']);
    }

    public function testAddMultipleInvitations(): void
    {
        $client = $this->makeClient(
            new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'message' => 'All good',
                    'errors' => [],
                ])
            )
        );

        $response = $client->survey('id')
            ->invitations()
            ->add(new InvitationRecipient('unit@test.com', 'Unit', 'Test', 'en', time() - 1000, time()))
            ->add(new InvitationRecipient('unit1@test.com', 'Unit1', 'Test1'))
            ->add(new InvitationRecipient('unit2@test.com', 'Unit2', 'Test2', 'et', time(), time() + 100))
            ->dispatch();

        static::assertEmpty($response->getPayload()['errors']);
        static::assertIsString($response->getPayload()['message']);
    }

    public function testFailAddingInvitationsDueToValidation(): void
    {
        $client = $this->makeClient(
            new ClientException(
                'Failed validation',
                new Request('post', '/'),
                new Response(
                    422,
                    [
                        'Content-Type' => 'application/json',
                    ],
                    json_encode([
                        'message' => 'Failed validation',
                        'errors' => [
                            'notanemail' => 'Not an email',
                        ],
                    ])
                )
            )
        );

        $this->expectException(ValidationException::class);

        $response = $client->survey('id')
            ->invitations()
            ->add(new InvitationRecipient('notanemail'))
            ->dispatch();
    }

    private function validator(): ServerRequestValidator
    {
        return (new ServerRequestValidatorFactory())->make();
    }

    /**
     * @param BadResponseException|ResponseInterface $handlerReturnValue
     */
    private function makeClient($handlerReturnValue): Client
    {
        $stackHandler = HandlerStack::create(new MockHandler([$handlerReturnValue]));
        $stackHandler->push(
            function ($handler) {
                return function (RequestInterface $request, $options) use ($handler) {
                    $this->validator()->validate(
                        new ServerRequest(
                            $request->getMethod(),
                            $request->getUri(),
                            $request->getHeaders(),
                            $request->getBody(),
                            $request->getProtocolVersion()
                        )
                    );

                    return $handler($request, $options);
                };
            }
        );

        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://localhost/api/v1/',
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer token',
            ],
            'handler' => $stackHandler,
        ]);

        return new Client($httpClient);
    }
}
