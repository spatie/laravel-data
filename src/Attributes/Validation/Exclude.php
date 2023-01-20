<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ExcludeIf;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exclude extends ValidationAttribute
{
    public function __construct(protected ?ExcludeIf $rule = null)
    {
    }

    public function getRules(ValidationPath $path): array
    {
        return [$this->rule ?? static::keyword()];
    }

    public static function keyword(): string
    {
        return 'exclude';
    }

    public static function create(string ...$parameters): static
    {
        return new static();
    }
}
