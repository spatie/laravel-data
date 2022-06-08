<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCastData extends Exception
{
    public static function shouldBeArray(string $modelClass, string $attribute): self
    {
        return new self("Attribute `{$attribute}` of model `{$modelClass}` should be an array");
    }

    public static function shouldBeData(string $modelClass, string $attribute): self
    {
        return new self("Attribute `{$attribute}` of model `{$modelClass}` should be a Data object");
    }

    public static function shouldBeTransformableData(string $modelClass, string $attribute): self
    {
        return new self("Attribute `{$attribute}` of model `{$modelClass}` should be a transformable Data object");
    }

    public static function dataCollectionTypeRequired(): self
    {
        return new self('When casting a Data Collection the type of Data should be provided as an argument');
    }
}
