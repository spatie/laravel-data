<?php

namespace Spatie\LaravelData\Tests\Fakes\ValidationAttributes;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\CustomValidationAttribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class PassThroughCustomValidationAttribute extends CustomValidationAttribute
{
    public function __construct(
        private array|object|string $rules,
    ) {
    }

    /**
     * @return array<object|string>|object|string
     */
    public function getRules(ValidationPath $path): array|object|string
    {
        return $this->rules;
    }
}
