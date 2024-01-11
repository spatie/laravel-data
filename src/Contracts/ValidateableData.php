<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Contracts\ValidateableData as ValidateableDataContract;

interface ValidateableData
{
    public static function validate(Arrayable|array $payload): Arrayable|array;

    /**
     * @return static
     */
    public static function validateAndCreate(Arrayable|array $payload): static;

    public static function withValidator(Validator $validator): void;
}
