<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;

trait ValidateableData
{
    public static function validate(Arrayable|array $payload): static
    {
        $rules = app(DataValidationRulesResolver::class)
            ->execute(static::class)
            ->merge(static::rules())
            ->toArray();

        $validator = ValidatorFacade::make(
            $payload instanceof Arrayable ? $payload->toArray() : $payload,
            $rules,
            static::messages(),
            static::attributes()
        );

        static::withValidator($validator);

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
