<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class ConfidentialDataCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): SimpleData
    {
        return SimpleData::from('CONFIDENTIAL');
    }
}
