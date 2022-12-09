<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

interface CommandLauncher
{
    public function launch(object $command): void;
}
