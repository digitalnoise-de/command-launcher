<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

final class IntCommand
{
    public function __construct(public readonly int $input)
    {
    }
}
