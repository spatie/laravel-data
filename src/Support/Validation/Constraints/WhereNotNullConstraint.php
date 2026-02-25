<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereNotNullConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
    ) {}

    public function toArray(): array
    {
        return [
            $this->normalizePossibleExternalReferenceParameter($this->column),
        ];
    }
}
