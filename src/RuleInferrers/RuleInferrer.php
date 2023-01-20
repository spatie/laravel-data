<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;

interface RuleInferrer
{
    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationPath $path,
    ): PropertyRules;
}
