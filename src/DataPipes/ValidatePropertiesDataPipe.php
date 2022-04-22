<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\DataClass;

class ValidatePropertiesDataPipe extends DataPipe
{
    public function __construct(
        protected DataValidatorResolver $dataValidatorResolver,
        protected bool $allTypes = false,
    ) {
    }

    public static function onlyRequests(): self
    {
        return new self(app(DataValidatorResolver::class), false);
    }

    public static function allTypes(): self
    {
        return new self(app(DataValidatorResolver::class), true);
    }

    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        if (! $initialValue instanceof Request && $this->allTypes === false) {
            return $properties;
        }

        $this->dataValidatorResolver->execute($class->name, $properties)->validate();

        return $properties;
    }
}
