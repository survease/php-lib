<?php

declare(strict_types=1);

namespace Survease\DTO;

use JsonSerializable;

class InvitationRecipient implements JsonSerializable
{
    public string $email;

    public ?string $firstName;

    public ?string $lastName;

    public ?string $language;

    public ?int $realDate;

    public ?int $dispatchAt;

    /**
     * @var array<string>
     */
    public array $tags = [];

    public function __construct(
        string $email,
        string $firstName = null,
        string $lastName = null,
        string $language = null,
        int $realDate = null,
        int $dispatchAt = null,
        array $tags = []
    ) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->language = $language;
        $this->realDate = $realDate;
        $this->dispatchAt = $dispatchAt;
        $this->tags = $tags;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'language' => $this->language,
            'realDate' => $this->realDate,
            'dispatchAt' => $this->dispatchAt,
            'tags' => $this->tags,
        ]);
    }
}
