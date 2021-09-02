<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rule implements DataValidationAttribute
{
    protected array $rules;

    public function __construct(string | array ...$rules)
    {
        $this->rules = array_reduce(
            $rules,
            fn (array $carry, array | string $new) => array_merge($carry, is_string($new) ? explode('|', $new) : $new),
            [],
        );
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
