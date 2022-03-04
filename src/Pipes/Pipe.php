<?php

namespace Spatie\LaravelData\Pipes;

use Closure;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

abstract class Pipe
{
    public abstract function pipe(
        mixed $initialValue,
        DataClass $class,
        Collection $properties,
    ): Closure|array;
}

/**
 * Serializers:
 * - Magic method (first run validation, authorization)
 * - Model
 * - Request
 * - Arrayable
 * - Array
 *
 * Pipes:
 * - AuthorizedPipe
 * - ValidatePropertiesPipe
 * - RenamePropertiesPipe
 * - CastPropertiesPipe
 */
