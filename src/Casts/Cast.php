<?php

namespace Spatie\LaravelData\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

interface Cast
{
    public function cast(DataProperty $property, mixed $value, Collection $properties, CreationContext $context): mixed;
}
