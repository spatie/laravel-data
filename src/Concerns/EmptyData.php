<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Resolvers\EmptyDataResolver;

trait EmptyData
{
    public static function empty(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra);
    }
}
