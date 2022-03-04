<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Password as BasePassword;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Password extends ValidationAttribute
{
    public function __construct(
        private int $min = 12,
        private bool $letters = false,
        private bool $mixedCase = false,
        private bool $numbers = false,
        private bool $symbols = false,
        private bool $uncompromised = false,
        private int $uncompromisedThreshold = 0,
        private bool $default = false,
    ) {
    }

    public function getRules(): array
    {
        if ($this->default) {
            return [BasePassword::default()];
        }

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
