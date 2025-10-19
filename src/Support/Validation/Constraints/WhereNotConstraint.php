<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use UnitEnum;

class WhereNotConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
        public readonly Arrayable|UnitEnum|array|string|ExternalReference $value,
    ) {}

    public function toArray(): array
    {
        return [
            $this->normalizePossibleExternalReferenceParameter($this->column),
            $this->normalizePossibleExternalReferenceParameter($this->value),
        ];
    }
}
