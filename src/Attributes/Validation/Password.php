<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Exception;
use Illuminate\Validation\Rules\Password as BasePassword;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Password extends ObjectValidationAttribute
{
    public function __construct(
        protected int|RouteParameterReference $min = 12,
        protected bool|RouteParameterReference $letters = false,
        protected bool|RouteParameterReference $mixedCase = false,
        protected bool|RouteParameterReference $numbers = false,
        protected bool|RouteParameterReference $symbols = false,
        protected bool|RouteParameterReference $uncompromised = false,
        protected int|RouteParameterReference $uncompromisedThreshold = 0,
        protected bool|RouteParameterReference $default = false,
        protected ?BasePassword $rule = null,
    ) {

    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        $min = $this->normalizePossibleRouteReferenceParameter($this->min);
        $letters = $this->normalizePossibleRouteReferenceParameter($this->letters);
        $mixedCase = $this->normalizePossibleRouteReferenceParameter($this->mixedCase);
        $numbers = $this->normalizePossibleRouteReferenceParameter($this->numbers);
        $symbols = $this->normalizePossibleRouteReferenceParameter($this->symbols);
        $uncompromised = $this->normalizePossibleRouteReferenceParameter($this->uncompromised);
        $uncompromisedThreshold = $this->normalizePossibleRouteReferenceParameter($this->uncompromisedThreshold);
        $default = $this->normalizePossibleRouteReferenceParameter($this->default);

        if ($default && $this->rule === null) {
            return $this->rule = BasePassword::default();
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

        return $this->rule = $rule;
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
