<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereNotConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
        public readonly mixed $value,
    ) {
    }

    public function apply(object $rule): void
    {
        $rule->whereNot(
            $this->normalizePossibleExternalReferenceParameter($this->column),
            $this->normalizePossibleExternalReferenceParameter($this->value),
        );
    }
}
