<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\DataProperty;

interface Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): mixed;
}
