<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Attributes\Concerns\NormalizesExternalReferences;

abstract class DatabaseConstraint implements Arrayable
{
    use NormalizesExternalReferences;
}
