<?php

namespace Spatie\LaravelData\Tests\Fakes\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomLaravelValidationRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
