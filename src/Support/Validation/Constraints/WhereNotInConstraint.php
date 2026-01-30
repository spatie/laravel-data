<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereNotInConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
        public readonly Arrayable|BackedEnum|array|ExternalReference $values,
    ) {}

    public function toArray(): array
    {
        return [
            $this->normalizePossibleExternalReferenceParameter($this->column),
            $this->normalizePossibleExternalReferenceParameter($this->values),
        ];
    }
}
