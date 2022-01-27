<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Routing\Pipeline;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

abstract class DataPipe
{
    // TODO: better names for parameters + move dataclass to constructor
    abstract public function execute(
        mixed $value,
        Collection $payload,
        DataClass $class,
    ): Collection;
}
