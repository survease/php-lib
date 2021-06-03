<?php

declare(strict_types=1);

namespace Survease\Api\Operations;

interface DispatchableResource
{
    public function method(): string;

    public function uri(): string;

    public function payload(): ?string;
}
