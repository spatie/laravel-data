<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

class WhereConstraint implements DatabaseConstraint
{
    public function __construct(
        public readonly mixed $column,
        public readonly mixed $value = null,
    ) {
    }

    public function toArray(): array
    {
        return [$this->column, $this->value];
    }
}
