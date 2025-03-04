<?php

namespace Spatie\LaravelData\Contracts;

interface PropertyMorphableData
{
    public static function morph(array $properties): ?string;
}
