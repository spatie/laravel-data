<?php

namespace Spatie\LaravelData\Casts;

interface Castable
{
    /**
     * Get the name of the caster class to use when casting to this cast target.
     *
     * @param  array  $arguments
     */
    public static function dataCastUsing(array $arguments): Cast;
}
