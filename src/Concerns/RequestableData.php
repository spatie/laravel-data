<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Validation\Validator;

trait RequestableData
{
    public static function rules(): array
    {
        return [];
    }

    public static function messages(): array
    {
        return [];
    }

    public static function attributes(): array
    {
        return [];
    }

    public static function withValidator(Validator $validator): void
    {
        return;
    }
}
