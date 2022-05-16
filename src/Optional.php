<?php

namespace Spatie\LaravelData;

class Optional
{
    public static function create(): Optional
    {
        return new self();
    }
}
