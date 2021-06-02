<?php

declare(strict_types=1);

namespace Survease\Operations;

use Survease\ApiResponse;

interface DispatchesRequests
{
    public function dispatch(): ApiResponse;

    public function method(): string;

    public function uri(): string;

    public function payload(): ?string;
}
