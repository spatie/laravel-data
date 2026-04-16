<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

class WhereInConstraint extends DatabaseConstraint
{
    public function __construct(
        public readonly string|ExternalReference $column,
        public readonly Arrayable|BackedEnum|array|ExternalReference $values,
    ) {
    }

    public function apply(object $rule): void
    {
        $rule->whereIn(
            $this->parseExternalReference($this->column),
            $this->parseExternalReference($this->values),
        );
    }
}
