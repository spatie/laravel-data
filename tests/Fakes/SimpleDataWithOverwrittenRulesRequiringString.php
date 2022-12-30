<?php

namespace Spatie\LaravelData\Tests\Fakes;

class SimpleDataWithOverwrittenRulesRequiringString extends SimpleData
{
    public static function rules(): array
    {
        return [
            'string' => ['string', 'required', 'min:10', 'max:100'],
        ];
    }
}
