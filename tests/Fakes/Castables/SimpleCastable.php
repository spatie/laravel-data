<?php

namespace Spatie\LaravelData\Tests\Fakes\Castables;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class SimpleCastable implements Castable
{
    public function __construct(public string $value)
    {
    }

    public static function dataCastUsing(...$arguments): Cast
    {
        return new class () implements Cast {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                return new SimpleCastable($value);
            }
        };
    }
}
