<?php

namespace Spatie\LaravelData\Casts;

use ReflectionNamedType;
use ReflectionProperty;

interface Cast
{
    public function cast(ReflectionNamedType $reflectionType, mixed $value): mixed;
}
