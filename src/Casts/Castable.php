<?php

namespace Spatie\LaravelData\Casts;

interface Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return class-string<Cast>|Cast
     */
    public static function castUsing(array $arguments);
}
