<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

abstract class AbstractParameterResolver implements ParameterResolver
{
    public const KEY_MANUAL = 'manual';

    abstract public function supports(string $class): bool;

    abstract public function options(string $class): array;

    /**
     * @param class-string $class
     */
    public function allOptions(string $class): array
    {
        $manual = new ParameterOption(self::KEY_MANUAL, 'Manual input');

        return [...$this->options($class), $manual];
    }

    abstract public function value(string $key): mixed;

    abstract public function manualValue(string $input): mixed;
}
