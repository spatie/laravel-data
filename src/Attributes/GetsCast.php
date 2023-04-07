<?php

namespace Spatie\LaravelData\Attributes;

use Spatie\LaravelData\Casts\Cast;

interface GetsCast
{
    public function get(): Cast;
}
