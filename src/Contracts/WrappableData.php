<?php

namespace Spatie\LaravelData\Contracts;

use Spatie\LaravelData\Support\Wrapping\Wrap;

interface WrappableData
{
    public function withoutWrapping(): WrappableData;

    public function wrap(string $key): WrappableData;

    public function getWrap(): Wrap;
}
