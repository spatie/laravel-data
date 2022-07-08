<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class ValidatePropertiesDataPipe implements DataPipe
{
    public function __construct(
        protected bool $allTypes = false,
    ) {
    }

    public static function onlyRequests(): self
    {
        return new self(false);
    }

    public static function allTypes(): self
    {
        return new self(true);
    }

    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request && $this->allTypes === false) {
            return $properties;
        }

        ($class->name)::validate($properties);

        return $properties;
    }
}
