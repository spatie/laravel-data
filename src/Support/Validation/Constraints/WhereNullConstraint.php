<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereNullConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
    ) {
    }

    public function apply(object $rule): void
    {
        $rule->whereNull(
            $this->parseExternalReference($this->column),
        );
    }
}
