<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\ResolverClass;

class Person
{
    public function __construct(public readonly string $firstName, public readonly string $lastName)
    {
    }
}
