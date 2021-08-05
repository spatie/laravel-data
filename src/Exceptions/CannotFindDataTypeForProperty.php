<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotFindDataTypeForProperty extends Exception
{
    public static function noDataReferenceFound(string $class, string $propertyName): self
    {
        return new self("Property `{$propertyName}` in `{$class}` is not a data object or collection");
    }

    public static function missingDataCollectionAnotation(string $class, string $propertyName): self
    {
        return new self("Data collection property `{$propertyName}` in `{$class}` is missing an annotation with the type of data it represents");
    }

    public static function wrongDataCollectionAnnotation(string $class, string $propertyName): self
    {
        return new self("Data collection property `{$propertyName}` in `{$class}` has an annotation that isn't a data object");
    }
}
