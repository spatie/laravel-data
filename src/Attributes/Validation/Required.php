<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\RequiredIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationRequiredIf;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required extends FoundationRequiredIf
{
    public function __construct()
    {
        parent::__construct(new RequiredIf(true));
    }

    public static function create(string ...$parameters): static
    {
        return new self();
    }
}
