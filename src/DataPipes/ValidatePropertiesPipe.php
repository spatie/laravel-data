<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\DataClass;

class ValidatePropertiesPipe extends DataPipe
{
    public function __construct(
        protected DataValidatorResolver $dataValidatorResolver
    ) {
    }

    public function execute(mixed $value, Collection $payload, DataClass $class, ): Collection
    {
        if (! $value instanceof Request) {
            return $payload;
        }

        $this->dataValidatorResolver->execute($class->name(), $payload)->validate();

        return $payload;
    }
}
