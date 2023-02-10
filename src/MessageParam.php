<?php
declare(strict_types=1);

namespace Digitalnoise\CommandLauncher;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class MessageParam
{
    public function __construct(private string $resolveClass, private string $param)
    {
    }

    public function resolveClass(): string
    {
        return $this->resolveClass;
    }

    public function setResolveClass(string $resolveClass): void
    {
        $this->resolveClass = $resolveClass;
    }

    public function param(): string
    {
        return $this->param;
    }

    public function setParam(string $param): void
    {
        $this->param = $param;
    }
}
