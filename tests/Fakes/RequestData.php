<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Closure;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Max;
use Spatie\LaravelData\Data;

class RequestData extends Data
{
    public static array $messages = [];
    public static array $attributes = [];
    public static array $rules = [];
    public static ?Closure $validatorClosure = null;
    public static bool $enableAuthorizeFailure = false;

    public function __construct(
        #[Max(10)]
        public string $string
    ) {
    }

    public static function rules(): array
    {
        return self::$rules;
    }

    public static function messages(): array
    {
        return self::$messages;
    }

    public static function attributes(): array
    {
        return self::$attributes;
    }

    public static function withValidator(Validator $validator): void
    {
        if (self::$validatorClosure) {
            (self::$validatorClosure)($validator);
        }
    }

    public static function authorized()
    {
        if (self::$enableAuthorizeFailure) {
            return false;
        }
    }

    public static function clear()
    {
        self::$attributes = [];
        self::$rules = [];
        self::$messages = [];
        self::$validatorClosure = null;
        self::$enableAuthorizeFailure = false;
    }
}
