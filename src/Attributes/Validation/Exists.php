<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Exists as BaseExists;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exists implements ValidationAttribute
{
    public function __construct(
        private string $table,
        private ?string $column = 'NULL',
    ) {
    }

    public function getRules(): array
    {
        return [new BaseExists($this->table, $this->column)];
    }
}
