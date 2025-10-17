<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

class WhereNotNullConstraint implements DatabaseConstraint
{
    public function __construct(
        public readonly mixed $column,
    ) {}

    public function toArray(): array
    {
        return [$this->column];
    }
}
