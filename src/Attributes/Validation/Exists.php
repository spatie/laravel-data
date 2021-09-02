<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Exists as BaseExists;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exists implements ValidationAttribute
{
    private array $rules;

    public function __construct(string $table, string $column = 'id')
    {
        $this->rules = [new BaseExists($table, $column)];
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
