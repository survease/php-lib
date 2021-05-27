<?php

declare(strict_types=1);

namespace Survease\Tests;

use PHPUnit\Framework\TestCase;
use Survease\Client;

class ClientTest extends TestCase
{
    public function testClientInitiation(): void
    {
        $client = new Client('myapikey');

        static::assertInstanceOf(Client::class, $client);
    }
}
