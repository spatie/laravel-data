<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MimeTypes implements ValidationAttribute
{
    use BuildsValidationRules;

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
