<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Exceptions\DataMissingFeature;

class DataFeature
{
    public static function require(object|string $object, string $feature): void
    {
        if (! static::has($object, $feature)) {
            throw DataMissingFeature::create(is_string($object) ? $object : $object::class, $feature);
        }
    }

    public static function has(object|string $object, string $feature): bool
    {
        return is_string($object)
            ? is_a($object, $feature, true)
            : $object instanceof $feature;
    }
}
