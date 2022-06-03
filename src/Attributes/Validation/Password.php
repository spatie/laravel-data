<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Password as BasePassword;
use Spatie\LaravelData\Support\Validation\Rules\FoundationPassword;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Password extends FoundationPassword
{
    public function __construct(
        int $min = 12,
        bool $letters = false,
        bool $mixedCase = false,
        bool $numbers = false,
        bool $symbols = false,
        bool $uncompromised = false,
        int $uncompromisedThreshold = 0,
        bool $default = false,
    ) {
        if ($default) {
            parent::__construct(BasePassword::default());

            return;
        }

        $rule = BasePassword::min($min);

        if ($letters) {
            $rule->letters();
        }

        if ($mixedCase) {
            $rule->mixedCase();
        }

        if ($numbers) {
            $rule->numbers();
        }

        if ($symbols) {
            $rule->symbols();
        }

        if ($uncompromised) {
            $rule->uncompromised($uncompromisedThreshold);
        }

        parent::__construct($rule);
    }
}
