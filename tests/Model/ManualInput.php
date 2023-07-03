<?php
declare(strict_types=1);

namespace Tests\Digitalnoise\CommandLauncher\Model;

final class ManualInput
{
    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
