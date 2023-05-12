<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataValidationRulesResolver;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
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
        $validator = app(DataValidatorResolver::class)->execute(static::class, $payload);

        try {
            $validator->validate();
        } catch (ValidationException $exception) {
            if (method_exists(static::class, 'redirect')) {
                $exception->redirectTo(app()->call([static::class, 'redirect']));
            }

            if (method_exists(static::class, 'redirectRoute')) {
                $exception->redirectTo(route(app()->call([static::class, 'redirectRoute'])));
            }

            if (method_exists(static::class, 'errorBag')) {
                $exception->errorBag(app()->call([static::class, 'errorBag']));
            }

            throw $exception;
        }

        return $validator->validated();
    }

    public static function validateAndCreate(Arrayable|array $payload): static
    {
        static::validate($payload);

        return static::from($payload);
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
            DataRules::create()
        );
    }
}
