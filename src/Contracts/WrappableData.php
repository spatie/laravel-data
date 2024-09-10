<?php

namespace Spatie\LaravelData\Contracts;

use Spatie\LaravelData\Support\Wrapping\Wrap;

interface WrappableData
{
    public function withoutWrapping();

    public function wrap(string $key);

    public function getWrap(): Wrap;
}
