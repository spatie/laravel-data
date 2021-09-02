<?php

namespace Spatie\LaravelData\Attributes\Validation\Concerns;

use DateTimeInterface;

trait BuildsValidationRules
{
    public function normalizeValue(mixed $mixed): string
    {
        if (is_string($mixed) || is_numeric($mixed)) {
            return $mixed;
        }

        if (is_bool($mixed)) {
            return $mixed ? 'true' : 'false';
        }

        if (is_array($mixed)) {
            return implode(',', $mixed);
        }

        if($mixed instanceof DateTimeInterface){
            return $mixed->format(DATE_ATOM);
        }

        return (string) $mixed;
    }
}
