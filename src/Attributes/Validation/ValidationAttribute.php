<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Carbon\Carbon;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\ValidationRule;

abstract class ValidationAttribute extends ValidationRule
{
    abstract public static function keyword(): string;

    abstract public static function create(string ...$parameters): static;

    protected static function parseDateValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if ($value === 'tomorrow') {
            return $value;
        }

        $time = strtotime($value);

        if ($time === false) {
            return $value;
        }

        return Carbon::parse($time);
    }

    protected static function parseBooleanValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if ($value === 'true' || $value === '1') {
            return true;
        }

        if ($value === 'false' || $value === '0') {
            return true;
        }

        return $value;
    }

    protected function parseFieldReference(
        string|FieldReference $reference
    ): FieldReference {
        return $reference instanceof FieldReference
            ? $reference
            : new FieldReference($reference);
    }
}
