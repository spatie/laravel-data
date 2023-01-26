<?php

namespace Spatie\LaravelData\Tests\Fakes\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule as RuleContract;

class CustomInvokableLaravelRule implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
    }
}
