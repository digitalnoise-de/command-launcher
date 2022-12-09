<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

final class BoolCommand
{
    public function __construct(public readonly bool $input)
    {
    }
}
