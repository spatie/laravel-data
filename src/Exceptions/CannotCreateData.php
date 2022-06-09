<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;
use Throwable;

class CannotCreateData extends Exception
{
    public static function noNormalizerFound(string $dataClass, mixed $value): self
    {
        $type = gettype($value);

        if ($type === 'object') {
            $type = $value::class;
        }

        return new self("Could not create a `{$dataClass}` object from value with type `{$type}`, no normalizer was found");
    }

    public static function constructorMissingParameters(
        DataClass $dataClass,
        Collection $parameters,
        Throwable $previous,
    ): self {
        $message = "Could not create `{$dataClass->name}`: the constructor requires {$dataClass->constructorMethod->parameters->count()} parameters, {$parameters->count()} given.";

        if ($parameters->isNotEmpty()) {
            $message .= "Parameters given: {$parameters->keys()->join(', ')}.";
        }

        return new self($message, previous: $previous);
    }
}
