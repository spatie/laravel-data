<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;

interface RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array;
}
