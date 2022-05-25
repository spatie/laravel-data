<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;
use Spatie\LaravelData\Support\Validation\Rules\FoundationNotIn;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotIn extends FoundationNotIn
{
    public function __construct(array|string ...$values)
    {
        parent::__construct(new BaseNotIn(Arr::flatten($values)));
    }
}
