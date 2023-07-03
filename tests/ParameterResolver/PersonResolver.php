<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\ParameterResolver;

use Digitalnoise\CommandLauncher\AbstractParameterResolver;
use Digitalnoise\CommandLauncher\ParameterOption;
use Digitalnoise\CommandLauncher\ParameterResolver;
use Tests\Digitalnoise\CommandLauncher\Model\ManualInput;
use Tests\Digitalnoise\CommandLauncher\ResolverClass\Person;

final class PersonResolver extends AbstractParameterResolver
{
    public function supports(string $class): bool
    {
        return $class === Person::class;
    }

    public function options(string $class): array
    {
        return [
            new ParameterOption('john', 'John Doe'),
            new ParameterOption('jane', 'Jane Doe'),
        ];
    }

    public function value(string $key): string
    {
        return match ($key) {
            'jane' => 'success',
            default => 'error'
        };
    }

    public function manualValue(string $input): mixed
    {
        return ManualInput::fromString($input);
    }
}
