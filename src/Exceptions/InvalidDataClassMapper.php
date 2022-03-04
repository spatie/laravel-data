<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Spatie\LaravelData\Mappers\Mapper;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

class InvalidDataClassMapper extends Exception
{
    public static function create(DataClass|DataProperty $target): self
    {
        $mapperClass = Mapper::class;

        $target = $target instanceof DataProperty
            ? "{$target->className}:{$target->name}"
            : $target->name;

        return new self("`MapFrom` attribute on `{$target}` should be a class implementing `{$mapperClass}`");
    }
}
