<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Support\Creation\CreationContext;

interface ValidateableData
{
    public static function validate(Arrayable|array $payload, ?CreationContext $context = null): Arrayable|array;

    /**
     * @return static
     */
    public static function validateAndCreate(Arrayable|array $payload, ?CreationContext $context = null): static;

    public static function withValidator(Validator $validator, ?CreationContext $context = null): void;
}
