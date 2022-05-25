<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Support\Validation\Rules\FoundationExists;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Exists extends FoundationExists
{
    public function __construct(
        string $table,
        ?string $column = 'NULL',
        ?string $connection = null,
        ?Closure $where = null,
    ) {
        $rule = new BaseExists(
            $connection ? "{$connection}.{$table}" : $table,
            $column
        );

        if ($where) {
            $rule->where($where);
        }

        parent::__construct($rule);
    }
}
