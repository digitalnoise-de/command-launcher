<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

final class StringCommand
{
    public function __construct(public readonly string $input)
    {
    }
}
