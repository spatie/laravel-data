<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ProhibitedIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationProhibitedIf;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibited extends FoundationProhibitedIf
{
    public function __construct()
    {
        parent::__construct(new ProhibitedIf(true));
    }

    public static function create(string ...$parameters): static
    {
        return new self();
    }
}
