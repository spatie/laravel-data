<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Exception;
use Illuminate\Validation\Rules\Password as BasePassword;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Password extends ValidationAttribute
{
    protected BasePassword $rule;

    public function __construct(
        int $min = 12,
        bool $letters = false,
        bool $mixedCase = false,
        bool $numbers = false,
        bool $symbols = false,
        bool $uncompromised = false,
        int $uncompromisedThreshold = 0,
        bool $default = false,
        ?BasePassword $rule = null,
    ) {
        if ($default && $rule === null) {
            $this->rule = BasePassword::default();

            return;
        }

        $rule ??= BasePassword::min($min);

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

        $this->rule = $rule;
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'password';
    }

    public static function create(string ...$parameters): static
    {
        throw new Exception('Cannot create a password rule');
    }
}
