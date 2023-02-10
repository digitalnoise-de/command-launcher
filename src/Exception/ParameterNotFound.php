<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher\Exception;

use Exception;

final class ParameterNotFound extends Exception
{
    public static function forAttributeParam(string $parameterName): self
    {
        return new self(sprintf('No parameter found for attribute param "%s".', $parameterName));
    }
}
