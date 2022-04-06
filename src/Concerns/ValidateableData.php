<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;

/**
 * @method static array rules(...$args)
 * @method static array messages(...$args)
 * @method static array attributes(...$args)
 */
trait ValidateableData
{
    public static function validate(Arrayable|array $payload): static
    {
        $validator = app(DataValidatorResolver::class)->execute(static::class, $payload);

        $validator->validate();

        return static::from($payload);
    }

    public static function withValidator(Validator $validator): void
    {
        return;
    }
}
