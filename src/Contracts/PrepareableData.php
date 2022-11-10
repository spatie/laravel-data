<?php

namespace Spatie\LaravelData\Contracts;

interface PrepareableData
{
    public static function prepareForPipeline(\Illuminate\Support\Collection $properties): \Illuminate\Support\Collection;
}
