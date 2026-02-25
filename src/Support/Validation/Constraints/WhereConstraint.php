<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Closure;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly Closure|string|ExternalReference $column,
        public readonly mixed $value = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            $this->normalizePossibleExternalReferenceParameter($this->column),
            $this->normalizePossibleExternalReferenceParameter($this->value),
        ];
    }
}
