<?php

namespace Spatie\LaravelData\Attributes\Validation;

use DateTimeInterface;
use Illuminate\Support\Arr;
use Stringable;

abstract class ValidationAttribute implements Stringable
{
    abstract public function getRules(): array;

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
}
