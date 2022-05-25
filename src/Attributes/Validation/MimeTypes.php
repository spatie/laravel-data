<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MimeTypes extends StringValidationAttribute
{
    private array $mimeTypes;

    public function __construct(string | array ...$mimeTypes)
    {
        $this->mimeTypes = Arr::flatten($mimeTypes);
    }

    public static function keyword(): string
    {
        return 'mimestypes';
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->mimeTypes)];
    }
}
