<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\ValidateableData as ValidateableDataContract;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;
use Spatie\LaravelData\Support\DataFeature;

/**
 * @method static array rules(...$args)
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
        DataFeature::require(static::class, BaseDataContract::class);

        return static::from(static::validate($payload));
    }

    public static function withValidator(Validator $validator): void
    {
        return;
    }
}
