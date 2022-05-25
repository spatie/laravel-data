<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ExcludeIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationExcludeIf;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exclude extends FoundationExcludeIf
{
    public function __construct(bool $value)
    {
        parent::__construct(new ExcludeIf($value));
    }
}
