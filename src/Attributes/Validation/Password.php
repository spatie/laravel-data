<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Password as BasePassword;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Password implements ValidationAttribute
{
    public function __construct(
        private int $min = 12,
        private bool $letters = true,
        private bool $mixedCase = true,
        private bool $numbers = true,
        private bool $symbols = true,
        private bool $uncompromised = true,
        private int $uncompromisedThreshold = 0
    ) {
    }

    public function getRules(): array
    {
        $rule = BasePassword::min($this->min);

        if ($this->letters) {
            $rule->letters();
        }

        if ($this->mixedCase) {
            $rule->mixedCase();
        }

        if ($this->numbers) {
            $rule->numbers();
        }

        if ($this->symbols) {
            $rule->symbols();
        }

        if ($this->uncompromised) {
            $rule->uncompromised($this->uncompromisedThreshold);
        }

        return [$rule];
    }
}
