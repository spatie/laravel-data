<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Support\Validation\Rules\FoundationUnique;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique extends FoundationUnique
{
    public function __construct(
        string $table,
        ?string $column = 'NULL',
        ?string $connection = null,
        ?string $ignore = null,
        ?string $ignoreColumn = null,
        bool $withoutTrashed = false,
        string $deletedAtColumn = 'deleted_at',
        ?Closure $where = null,
    ) {
        $rule = new BaseUnique(
            $connection ? "{$connection}.{$table}" : $table,
            $column
        );

        if ($withoutTrashed) {
            $rule->withoutTrashed($deletedAtColumn);
        }

        if ($ignore) {
            $rule->ignore($ignore, $ignoreColumn);
        }

        if ($where) {
            $rule->where($where);
        }

        parent::__construct($rule);
    }
}
