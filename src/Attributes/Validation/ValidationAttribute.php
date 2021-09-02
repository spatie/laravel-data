<?php

namespace Spatie\LaravelData\Attributes\Validation;

interface ValidationAttribute
{
    public function getRules(): array;
}
