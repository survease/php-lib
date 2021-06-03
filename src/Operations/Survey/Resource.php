<?php

declare(strict_types=1);

namespace Survease\Api\Operations\Survey;

class Resource
{
    private string $surveyId;

    public function __construct(string $id)
    {
        $this->surveyId = $id;
    }

    public function invitations(): Invitations
    {
        return new Invitations('survey/' . $this->surveyId);
    }
}
