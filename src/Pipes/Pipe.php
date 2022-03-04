<?php

namespace Spatie\LaravelData\Pipes;

use Closure;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

abstract class Pipe
{
    abstract public function pipe(
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
