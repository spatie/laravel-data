<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class MappedValidationExceptionResolver
{
    public function execute(
        ValidationException $exception,
        array $mappedProperties,
    ): ValidationException {
        if (! $exception->validator instanceof Validator) {
            return $exception;
        }

        dd($mappedProperties);

        $messageBag = $exception->validator->errors();

        $messages = $messageBag->getMessages();

        $newMessages = [];

        foreach ($mappedProperties as $original => $mapped) {
            if (! array_key_exists($original, $messages)) {
                continue;
            }

            $messageBag->forget($original);

            $newMessages[$mapped] = $messages[$original];
        }

        $messageBag->merge($newMessages);

        return $exception;
    }
}
