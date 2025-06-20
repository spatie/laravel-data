<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Resolvers\EmptyDataResolver;

trait EmptyData
{
    public static function empty(array $extra = [], mixed $replaceNullValuesWith = null, array $except = []): array
    {
        $emptyData = app(EmptyDataResolver::class)->execute(static::class, $extra, $replaceNullValuesWith);
        if (count($except) === 0) {
            return $emptyData;
        }

        return array_diff_key($emptyData, array_flip($except));
    }
}
