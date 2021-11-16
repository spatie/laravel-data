<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;

trait ValidateableData
{
    public static function validate(Arrayable|array $payload): static
    {
        $validator = app(DataValidatorResolver::class)->execute(static::class, $payload);

        $validator->validate();

        return static::from($payload);
    }

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
