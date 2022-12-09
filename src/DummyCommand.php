<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

final class DummyCommand
{
    public function __construct(
        public readonly string          $name,
        public readonly ParameterOption $special,
        public readonly bool            $bier,
        public readonly \DateTime       $zeit
    ) {
    }
}
