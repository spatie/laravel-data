<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;

class CannotCreateCastAttribute extends Exception
{
    public static function notACast(): self
    {
        $cast = Cast::class;

        return new self("WithCast attribute needs a cast that implements `{$cast}`");
    }

    public static function notACastable(): self
    {
        $cast = Castable::class;

        return new self("WithCastable attribute needs a class that implements `{$cast}`");
    }
}
