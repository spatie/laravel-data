<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Resolvers\EmptyDataResolver;

trait EmptyStringData
{
    use EmptyData;
    public static function emptyString(array $extra = []): array
    {
        return app(EmptyDataResolver::class)->execute(static::class, $extra, '');
    }
}
