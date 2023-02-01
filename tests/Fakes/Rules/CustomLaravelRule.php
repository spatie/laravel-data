<?php

namespace Spatie\LaravelData\Tests\Fakes\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class CustomLaravelRule implements RuleContract
{
    public function passes($attribute, $value)
    {
    }

    public function message()
    {
    }
}

;
