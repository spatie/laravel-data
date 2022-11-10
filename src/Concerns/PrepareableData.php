<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Support\Collection;

trait PrepareableData
{
    public static function prepareForPipeline(Collection $properties): Collection
    {
        return $properties;
    }
}
