<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class DataValidatorResolver
{
    /** @param class-string<DataObject> $dataClass */
    public function execute(string $dataClass, Arrayable|array $payload): Validator
    {
        $payload = $payload instanceof Arrayable ? $payload->toArray() : $payload;

        $dataRules = app(DataValidationRulesResolver::class)->execute(
            $dataClass,
            $payload,
            ValidationPath::create(),
            DataRules::create()
        );

        $validator = ValidatorFacade::make(
            $payload,
            $dataRules->rules,
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
