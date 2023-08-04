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
    protected BasePassword $rule;

    public function __construct(
        int|RouteParameterReference  $min = 12,
        bool|RouteParameterReference $letters = false,
        bool|RouteParameterReference $mixedCase = false,
        bool|RouteParameterReference $numbers = false,
        bool|RouteParameterReference $symbols = false,
        bool|RouteParameterReference $uncompromised = false,
        int|RouteParameterReference  $uncompromisedThreshold = 0,
        bool|RouteParameterReference $default = false,
        ?BasePassword                $rule = null,
    ) {
        $min = $this->normalizePossibleRouteReferenceParameter($min);
        $letters = $this->normalizePossibleRouteReferenceParameter($letters);
        $mixedCase = $this->normalizePossibleRouteReferenceParameter($mixedCase);
        $numbers = $this->normalizePossibleRouteReferenceParameter($numbers);
        $symbols = $this->normalizePossibleRouteReferenceParameter($symbols);
        $uncompromised = $this->normalizePossibleRouteReferenceParameter($uncompromised);
        $uncompromisedThreshold = $this->normalizePossibleRouteReferenceParameter($uncompromisedThreshold);
        $default = $this->normalizePossibleRouteReferenceParameter($default);

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

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
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
