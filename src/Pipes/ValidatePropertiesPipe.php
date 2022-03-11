<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\DataClass;

class ValidatePropertiesPipe extends Pipe
{
    public function __construct(
        protected DataValidatorResolver $dataValidatorResolver,
        protected bool $allTypes = false,
    ) {
    }

    public static function onlyRequests(): static
    {
        return new static(app(DataValidationRulesResolver::class), false);
    }

    public static function allTypes(): static
    {
        return new static(app(DataValidationRulesResolver::class), true);
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
