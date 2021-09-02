<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Illuminate\Validation\Rules\Unique as BaseUnique;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique implements ValidationAttribute
{
    public function __construct(
        private string $table,
        private ?string $column = 'NULL',
        private ?string $connection = null,
        private ?string $ignore = null,
        private ?string $ignoreColumn = null,
        private bool $withoutTrashed = false,
        private string $deletedAtColumn = 'deleted_at',
        private ?Closure $where = null,
    ) {
    }

    public function getRules(): array
    {
        $rule = new BaseUnique(
            $this->connection ? "{$this->connection}.{$this->table}" : $this->table,
            $this->column
        );

        if ($this->withoutTrashed) {
            $rule->withoutTrashed($this->deletedAtColumn);
        }

        if ($this->ignore) {
            $rule->ignore($this->ignore, $this->ignoreColumn);
        }

        if ($this->where) {
            $rule->where($this->where);
        }

        return [$rule];
    }
}
