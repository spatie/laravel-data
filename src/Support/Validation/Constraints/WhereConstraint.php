<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use UnitEnum;

class WhereConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly Closure|string|ExternalReference $column,
        public readonly Arrayable|UnitEnum|Closure|array|string|int|bool|null|ExternalReference $value = null,
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
