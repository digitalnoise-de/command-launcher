<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

final class ParameterOption
{
    public function __construct(public readonly string $key, public readonly string $label)
    {
    }
}
