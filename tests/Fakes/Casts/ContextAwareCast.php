<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class ContextAwareCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        return $value . '+' . json_encode($context);
    }
}
