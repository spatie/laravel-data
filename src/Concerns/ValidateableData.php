<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\Validation\DataRules;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationPath;

/**
 * @method static array rules(ValidationContext $context)
 * @method static array messages(...$args)
 * @method static array attributes(...$args)
 * @method static bool stopOnFirstFailure()
 * @method static string redirect()
 * @method static string redirectRoute()
 * @method static string errorBag()
 */
trait ValidateableData
{
    public static function validate(Arrayable|array $payload): Arrayable|array
    {
        $validator = DataContainer::get()->dataValidatorResolver()->execute(
            static::class,
            $payload,
        );

        return DataContainer::get()->validatedPayloadResolver()->execute(
            static::class,
            $validator,
        );
    }

    public static function validateAndCreate(Arrayable|array $payload): static
    {
        return static::factory()->alwaysValidate()->from($payload);
    }

    public static function withValidator(Validator $validator): void
    {
        return;
    }

    public static function getValidationRules(array $payload): array
    {
        return app(DataValidationRulesResolver::class)->execute(
            static::class,
            $payload,
            ValidationPath::create(),
            DataRules::create(),
        );
    }
}
