<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\In as BaseIn;
use Spatie\LaravelData\Support\Validation\Rules\FoundationIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class In extends FoundationIn
{
    public function __construct(array|string ...$values)
    {
        parent::__construct(new BaseIn(Arr::flatten($values)));
    }
}
