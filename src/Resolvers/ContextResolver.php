<?php

namespace Spatie\LaravelData\Resolvers;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ContextResolver
{
    /** @var array<string, Context> */
    protected array $contexts = [];

    public function execute(ReflectionProperty|ReflectionClass|ReflectionMethod $reflection): Context
    {
        $reflectionClass = $reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod
            ? $reflection->getDeclaringClass()
            : $reflection;

        return $this->contexts[$reflectionClass->getName()] ??= (new ContextFactory())->createFromReflector($reflectionClass);
    }
}
