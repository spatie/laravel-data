<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Undefined;

class DataValidatorResolver
{
    public function __construct(protected DataValidationRulesResolver $dataValidationRulesResolver)
    {
    }

    /** @param class-string<\Spatie\LaravelData\Data> $dataClass */
    public function execute(string $dataClass, Arrayable|array $payload): Validator
    {
        $rules = app(DataValidationRulesResolver::class)
            ->execute($dataClass)
            ->toArray();

        $properties = array_filter(
            $payload instanceof Arrayable ? $payload->toArray() : $payload,
            fn(mixed $item) => ! $item instanceof Undefined
        );

        $validator = ValidatorFacade::make(
            $properties,
            $rules,
            $dataClass::messages(),
            $dataClass::attributes()
        );

        $dataClass::withValidator($validator);

        return $validator;
    }
}
