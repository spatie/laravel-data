<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Contracts\DataObject;

class DataValidatorResolver
{
    public function __construct(protected DataClassValidationRulesResolver $dataValidationRulesResolver)
    {
    }

    /** @param class-string<DataObject> $dataClass */
    public function execute(string $dataClass, Arrayable|array $payload): Validator
    {
        $payload = $payload instanceof Arrayable ? $payload->toArray() : $payload;

        $rules = app(DataClassValidationRulesResolver::class)
            ->execute($dataClass, $payload)
            ->toArray();

        $validator = ValidatorFacade::make(
            $payload,
            $rules,
            method_exists($dataClass, 'messages') ? app()->call([$dataClass, 'messages']) : [],
            method_exists($dataClass, 'attributes') ? app()->call([$dataClass, 'attributes']) : []
        );

        if (method_exists($dataClass, 'stopOnFirstFailure')) {
            $validator->stopOnFirstFailure(app()->call([$dataClass, 'stopOnFirstFailure']));
        }

        $dataClass::withValidator($validator);

        return $validator;
    }
}
