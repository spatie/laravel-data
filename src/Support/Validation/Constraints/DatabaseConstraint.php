<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelData\Attributes\Concerns\NormalizesExternalReferences;

abstract class DatabaseConstraint
{
    use NormalizesExternalReferences;

    /** @param Exists|Unique $rule */
    abstract public function apply(object $rule): void;
}
