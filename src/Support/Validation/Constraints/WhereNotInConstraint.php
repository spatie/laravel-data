<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

class WhereNotInConstraint implements DatabaseConstraint
{
    public function __construct(
        public readonly mixed $column,
        public readonly mixed $values,
    ) {}

    public function toArray(): array
    {
        return [$this->column, $this->values];
    }
}
