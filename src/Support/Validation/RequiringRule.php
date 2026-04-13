<?php

namespace Spatie\LaravelData\Support\Validation;

interface RequiringRule
{
    public function appliesToContext(?string $currentContext): bool;
}
