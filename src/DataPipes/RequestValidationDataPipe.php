<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class RequestValidationDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request) {
            return $properties;
        }

        $className = $class->name;
        if (method_exists($className, 'validationData')) {
            return Collection::make($className::validationData($payload));
        }

        return $properties;
    }
}
