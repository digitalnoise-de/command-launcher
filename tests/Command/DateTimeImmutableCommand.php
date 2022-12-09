<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Command;

use DateTimeImmutable;

final class DateTimeImmutableCommand
{
    public function __construct(public readonly DateTimeImmutable $input)
    {
    }
}
