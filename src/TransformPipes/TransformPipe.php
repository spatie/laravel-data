<?php

namespace Spatie\LaravelData\TransformPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

abstract class TransformPipe
{
    abstract public function handle(
        Data $data,
        DataClass $class,
        Collection $properties,
    ): Collection;
}
