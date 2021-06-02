<?php

declare(strict_types=1);

namespace Survease\Operations\Survey;

use Survease\ApiResponse;
use Survease\Client;
use Survease\DTO\InvitationRecipient;
use Survease\Operations\DispatchesRequests;

class Invitations implements DispatchesRequests
{
    /**
     * @var array<InvitationRecipient>
     */
    private array $recipients = [];

    private Client $client;

    private string $prefix;

    public function __construct(Client $client, string $prefix)
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    public function add(InvitationRecipient $recipient): self
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    public function dispatch(): ApiResponse
    {
        return $this->client->makeRequest($this);
    }

    public function payload(): ?string
    {
        return json_encode($this->recipients, JSON_THROW_ON_ERROR);
    }

    public function method(): string
    {
        return 'post';
    }

    public function uri(): string
    {
        return $this->prefix . '/invitations';
    }
}
