<?php

namespace Spatie\LaravelData\Casts;

use ReflectionNamedType;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;

interface Cast
{
    public function cast(DataProperty $property, mixed $value): mixed;
}
