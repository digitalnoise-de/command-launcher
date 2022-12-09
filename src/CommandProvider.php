<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

interface CommandProvider
{
    /**
     * @return list<class-string>
     */
    public function all(): array;
}
