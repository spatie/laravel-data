<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class ConfidentialDataCast implements Cast
{
    public function cast(DataProperty $property, mixed $value): SimpleData
    {
        return SimpleData::from('CONFIDENTIAL');
    }
}
