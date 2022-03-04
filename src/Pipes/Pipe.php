<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

abstract class Pipe
{
    public abstract function handle(
        mixed $initialValue,
        DataClass $class,
        Collection $properties,
    ): Collection|Data;
}
