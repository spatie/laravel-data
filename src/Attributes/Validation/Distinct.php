<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Distinct implements ValidationAttribute
{
    public const Strict = 'strict';
    public const IgnoreCase = 'ignore_case';

    public function __construct(private ?string $mode = null)
    {
    }

    public function getRules(): array
    {
        if ($this->mode === null) {
            return ['distinct'];
        }

        if (! in_array($this->mode, [self::IgnoreCase, self::Strict])) {
            throw CannotBuildValidationRule::create('Distinct mode should be ignore_case or strict.');
        }

        return ["distinct:{$this->mode}"];
    }
}
