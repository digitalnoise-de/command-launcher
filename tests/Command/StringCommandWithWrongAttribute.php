<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

use Digitalnoise\CommandLauncher\MessageParam;
use Tests\Digitalnoise\CommandLauncher\ResolverClass\Person;

final class StringCommandWithWrongAttribute
{
    #[MessageParam(resolveClass: Person::class, param: 'inpu')]
    public function __construct(public readonly string $input)
    {
    }
}
