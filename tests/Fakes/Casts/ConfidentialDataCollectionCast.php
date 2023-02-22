<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class ConfidentialDataCollectionCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): array
    {
        return array_map(fn () => SimpleData::from('CONFIDENTIAL'), $value);
    }
}
