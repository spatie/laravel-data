<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotFindDataClass extends Exception
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
        return new self("Data collection property `{$propertyName}` in `{$class}` has an annotation that isn't a data object or is missing an annotation");
    }

    public static function cannotReadReflectionParameterDocblock(string $class, string $parameter): self
    {
        return new self("Data collection reflection parameter `{$parameter}` in `{$class}::__constructor` has an annotation that isn't a data object or is missing an annotation");
    }
}
