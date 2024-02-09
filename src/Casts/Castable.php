<?php

namespace Spatie\LaravelData\Casts;

interface Castable
{
    /**
     * @param array $arguments
     */
    public static function dataCastUsing(array $arguments): Cast;
}
