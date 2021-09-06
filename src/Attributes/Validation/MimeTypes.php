<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MimeTypes extends ValidationAttribute
{
    private array $mimeTypes;

    public function __construct(string | array $mimeTypes)
    {
        $this->mimeTypes = is_string($mimeTypes) ? [$mimeTypes] : $mimeTypes;
    }

    public function getRules(): array
    {
        return ["mimestypes:{$this->normalizeValue($this->mimeTypes)}"];
    }
}
