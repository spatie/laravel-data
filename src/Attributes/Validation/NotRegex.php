<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotRegex implements ValidationAttribute
{
    public function __construct(private string $pattern)
{

    public function getRules(): array
    {
        return ['not_regex:' . $this->pattern];
    }
}
