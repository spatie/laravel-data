<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Spatie\LaravelData\Transformers\Transformer;

class CannotCreateTransformerAttribute extends Exception
{
    public static function notATransformer(): self
    {
        $transformer = Transformer::class;

        return new self("WithTransformer attribute needs a transformer that implements `{$transformer}`");
    }
}
