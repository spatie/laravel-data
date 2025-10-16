<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MimeTypes extends StringValidationAttribute
{
    protected array $mimeTypes;

    public function __construct(string|array|ExternalReference ...$mimeTypes)
    {
        $this->mimeTypes = Arr::flatten($mimeTypes);
    }

    public static function keyword(): string
    {
        return 'mimetypes';
    }

    public function parameters(): array
    {
        return [$this->mimeTypes];
    }
}
