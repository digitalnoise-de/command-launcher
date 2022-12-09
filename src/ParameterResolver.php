<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

interface ParameterResolver
{
    /**
     * @param class-string $class
     */
    public function supports(string $class): bool;

    /**
     * @param class-string $class
     *
     * @return list<ParameterOption>
     */
    public function options(string $class): array;

    public function value(string $key): mixed;
}
