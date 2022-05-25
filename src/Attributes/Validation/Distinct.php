<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Exceptions\CannotBuildValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Distinct extends StringValidationAttribute
{
    public const Strict = 'strict';
    public const IgnoreCase = 'ignore_case';

    public function __construct(private ?string $mode = null)
    {
    }

    public static function keyword(): string
    {
        return 'distinct';
    }

    public function parameters(): array
    {
        if ($this->mode === null) {
            return [];
        }

        if (! in_array($this->mode, [self::IgnoreCase, self::Strict])) {
            throw CannotBuildValidationRule::create('Distinct mode should be ignore_case or strict.');
        }

        return [$this->mode];
    }
}
