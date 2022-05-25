<?php

namespace Spatie\LaravelData\Attributes\Validation;

abstract class ObjectValidationAttribute extends ValidationAttribute
{
    abstract public static function keyword(): string;
}
