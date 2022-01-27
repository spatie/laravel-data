<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

abstract class DataPipe
{
    // TODO: better names for parameters + move dataclass to constructor
    abstract public function execute(
        mixed $value,
        Collection $payload,
        DataClass $class,
    ): Collection;
}
