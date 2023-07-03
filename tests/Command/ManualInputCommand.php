<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

use Digitalnoise\CommandLauncher\MessageParam;
use Tests\Digitalnoise\CommandLauncher\Model\ManualInput;
use Tests\Digitalnoise\CommandLauncher\ResolverClass\Person;

final class ManualInputCommand
{
    #[MessageParam(resolveClass: Person::class, param: 'input')]
    public function __construct(public readonly ManualInput $input)
    {
    }
}
