<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RulesCollection;

interface RuleInferrer
{
    public function handle(
        DataProperty $property,
        RulesCollection $rules,
        ?string $path,
    ): RulesCollection;
}
