<?php

namespace Spatie\LaravelData\Support\Validation;

use DateTimeInterface;
use Stringable;

abstract class ValidationRule
{
    abstract public function getRules(): array;
}
