<?php

declare(strict_types=1);

namespace Survease\Api\Operations\Survey;

use Survease\Api\DTO\InvitationRecipient;
use Survease\Api\Operations\DispatchableResource;

class Invitations implements DispatchableResource
{
    /**
     * @var array<InvitationRecipient>
     */
    private array $recipients = [];

    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function add(InvitationRecipient $recipient): self
    {
        $this->recipients[] = $recipient;

        return $this;
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
