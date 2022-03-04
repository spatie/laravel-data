<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

class RenamePropertiesPipe extends Pipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties,): Collection|Data
    {
        return $properties;
    }
}
