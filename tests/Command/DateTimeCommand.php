<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

use DateTime;

final class DateTimeCommand
{
    public function __construct(public readonly DateTime $input)
    {
    }
}
