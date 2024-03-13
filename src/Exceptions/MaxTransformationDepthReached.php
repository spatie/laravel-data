<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class MaxTransformationDepthReached extends Exception
{
    public static function create(int $depth): self
    {
        return new self("Max transformation depth of {$depth} reached.");
    }
}
