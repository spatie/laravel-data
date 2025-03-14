<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Resolvers\EmptyDataResolver;

trait EmptyData
{
    public static function empty(array $extra = [], mixed $replaceNullValuesWith = null): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra, $replaceNullValuesWith);
    }
}
