<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Illuminate\Validation\Rules\Unique as BaseUnique;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exists extends ValidationAttribute
{
    public function __construct(
        private string $table,
        private ?string $column = 'NULL',
        private ?string $connection = null,
        private ?Closure $where = null,
    ) {
    }

    public function getRules(): array
    {
        $rule = new BaseExists(
            $this->connection ? "{$this->connection}.{$this->table}" : $this->table,
            $this->column
        );

        if ($this->where) {
            $rule->where($this->where);
        }

        return [$rule];
    }
}
