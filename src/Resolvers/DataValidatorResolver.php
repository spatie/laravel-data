<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

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
            ->merge($dataClass::rules())
            ->toArray();

        $validator = ValidatorFacade::make(
            $payload instanceof Arrayable ? $payload->toArray() : $payload,
            $rules,
            $dataClass::messages(),
            $dataClass::attributes()
        );

        $dataClass::withValidator($validator);

        return $validator;
    }
}
