<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Exception;
use Illuminate\Validation\Rules\Password as BasePassword;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Password extends ObjectValidationAttribute
{
    public function __construct(
        protected int|ExternalReference $min = 12,
        protected bool|ExternalReference $letters = false,
        protected bool|ExternalReference $mixedCase = false,
        protected bool|ExternalReference $numbers = false,
        protected bool|ExternalReference $symbols = false,
        protected bool|ExternalReference $uncompromised = false,
        protected int|ExternalReference $uncompromisedThreshold = 0,
        protected bool|ExternalReference $default = false,
        protected ?BasePassword $rule = null,
    ) {

    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        $min = $this->normalizePossibleExternalReferenceParameter($this->min);
        $letters = $this->normalizePossibleExternalReferenceParameter($this->letters);
        $mixedCase = $this->normalizePossibleExternalReferenceParameter($this->mixedCase);
        $numbers = $this->normalizePossibleExternalReferenceParameter($this->numbers);
        $symbols = $this->normalizePossibleExternalReferenceParameter($this->symbols);
        $uncompromised = $this->normalizePossibleExternalReferenceParameter($this->uncompromised);
        $uncompromisedThreshold = $this->normalizePossibleExternalReferenceParameter($this->uncompromisedThreshold);
        $default = $this->normalizePossibleExternalReferenceParameter($this->default);

        if ($default && $this->rule === null) {
            return BasePassword::default();
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

        return $rule;
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
