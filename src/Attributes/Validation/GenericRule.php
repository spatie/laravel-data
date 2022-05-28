<?php

namespace Spatie\LaravelData\Attributes\Validation;

trait GenericRule
{
    public static function keyword(): string
    {
        return Str(__CLASS__)->classBasename()->snake();
    }

    public function parameters(): array
    {
        return [];
    }
}
