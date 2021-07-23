<?php

namespace Spatie\LaravelData\AutoRules;

use Closure;
use Spatie\LaravelData\Support\DataProperty;

interface AutoRule
{
    public function handle(DataProperty $property, array $rules): array;
}
