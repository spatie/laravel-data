<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

class WhereNotConstraint implements DatabaseConstraint
{
    public function __construct(
        public readonly mixed $column,
        public readonly mixed $value,
    ) {}

    public function toArray(): array
    {
        return [$this->column, $this->value];
    }
}
