<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MimeTypes implements ValidationAttribute
{
    private array $mimeTypes;

    public function __construct(string ...$mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function getRules(): array
    {
        return ['mimetypes:' . implode(',', $this->mimeTypes)];
    }
}
