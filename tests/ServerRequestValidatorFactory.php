<?php

declare(strict_types=1);

namespace Survease\Tests;

use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class ServerRequestValidatorFactory
{
    private static ValidatorBuilder $builder;

    public function __construct()
    {
        static::$builder = new ValidatorBuilder();
        static::$builder->fromJsonFile(__DIR__ . '/api-docs.json');
    }

    public function make(): ServerRequestValidator
    {
        return static::$builder->getServerRequestValidator();
    }
}
