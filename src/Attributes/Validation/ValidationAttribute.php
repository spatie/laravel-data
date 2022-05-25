<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Carbon\Carbon;
use DateTimeInterface;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Stringable;

abstract class ValidationAttribute extends ValidationRule implements Stringable
{
    abstract public function getRules(): array;

    abstract public static function create(string ...$parameters): static;

    public function __toString(): string
    {
        return implode('|', $this->getRules());
    }

    protected function normalizeValue(mixed $mixed): string
    {
        if (is_string($mixed) || is_numeric($mixed)) {
            return (string) $mixed;
        }

        if (is_bool($mixed)) {
            return $mixed ? 'true' : 'false';
        }

        if (is_array($mixed)) {
            return implode(',', $mixed);
        }

        if ($mixed instanceof DateTimeInterface) {
            return $mixed->format(DATE_ATOM);
        }

        return (string) $mixed;
    }

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
}
