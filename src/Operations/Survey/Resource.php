<?php

declare(strict_types=1);

namespace Survease\Operations\Survey;

use Survease\Client;

class Resource
{
    private string $surveyId;

    private Client $client;

    public function __construct(Client $client, string $id)
    {
        $this->surveyId = $id;
        $this->client = $client;
    }

    public function invitations(): Invitations
    {
        return new Invitations($this->client, 'survey/' . $this->surveyId);
    }
}
