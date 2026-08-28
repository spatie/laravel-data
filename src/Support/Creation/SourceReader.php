<?php

namespace Spatie\LaravelData\Support\Creation;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataProperty;

class SourceReader
{
    public static function read(
        array|Normalized $source,
        string|int $key,
        DataProperty $property
    ): mixed {
        if ($source instanceof Normalized) {
            return $source->getProperty((string) $key, $property);
        }

        if (! array_key_exists($key, $source)) {
            return UnknownProperty::create();
        }

        return $source[$key];
    }

    /**
     * @param array<int, array|Normalized> $sources
     */
    public static function readFromMany(
        array $sources,
        string|int $key,
        DataProperty $property
    ): mixed {
        $result = UnknownProperty::create();

        foreach ($sources as $source) {
            $value = static::read($source, $key, $property);

            if ($value instanceof UnknownProperty) {
                continue;
            }

            if ($result instanceof UnknownProperty) {
                $result = $value;

                continue;
            }

            if ($value === null || $value instanceof Optional) {
                continue;
            }

            $result = $value;
        }

        return $result;
    }
}
