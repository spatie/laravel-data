<?php

namespace Spatie\LaravelData\Attributes\Validation;

interface DataValidationAttribute
{
    public function getRules(): array;
}
