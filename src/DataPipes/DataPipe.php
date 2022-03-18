<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

abstract class DataPipe
{
    abstract public function handle(
        mixed $initialValue,
        DataClass $class,
        Collection $properties,
    ): Collection;
}
