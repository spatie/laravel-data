<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\ValidateableData;

class ValidatedPayloadResolver
{
    public function __construct(
        protected MappedValidationExceptionResolver $mappedValidationExceptionResolver
    ) {
    }

    /** @param class-string<ValidateableData&BaseData> $dataClass */
    public function execute(
        string $dataClass,
        Validator $validator,
        array $mappedProperties,
    ): array {
        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            if (count($mappedProperties) !== 0) {
                $this->mappedValidationExceptionResolver->execute($exception, $mappedProperties);
            }

            if (method_exists($dataClass, 'redirect')) {
                $exception->redirectTo(app()->call([$dataClass, 'redirect']));
            }

            if (method_exists($dataClass, 'redirectRoute')) {
                $exception->redirectTo(route(app()->call([$dataClass, 'redirectRoute'])));
            }

            if (method_exists($dataClass, 'errorBag')) {
                $exception->errorBag(app()->call([$dataClass, 'errorBag']));
            }

            throw $exception;
        }

        return $validator->validated();
    }
}
