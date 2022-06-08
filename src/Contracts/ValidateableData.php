<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;

interface ValidateableData
{
    public static function validate(Arrayable|array $payload): Arrayable|array;

    public static function validateAndCreate(Arrayable|array $payload): object;

    public static function withValidator(Validator $validator): void;
}
