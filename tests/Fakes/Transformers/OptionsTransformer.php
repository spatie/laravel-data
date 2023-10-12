<?php

namespace Spatie\LaravelData\Tests\Fakes\Transformers;

use BackedEnum;
use Spatie\LaravelData\Contracts\ExcludeFromEloquentCasts;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\Interfaces\HasOptions;
use Spatie\LaravelData\Transformers\Transformer;

class OptionsTransformer implements Transformer, ExcludeFromEloquentCasts
{
    public function transform(DataProperty $property, mixed $value): ?array
    {
        if (! $value instanceof BackedEnum || ! $value instanceof HasOptions) {
            return null;
        }

        return $value->toOptions();
    }
};
