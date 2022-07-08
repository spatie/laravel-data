<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Exception;
use Illuminate\Validation\Rules\Unique as BaseUnique;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique extends ValidationAttribute
{
    protected BaseUnique $rule;

    public function __construct(
        ?string $table = null,
        ?string $column = 'NULL',
        ?string $connection = null,
        ?string $ignore = null,
        ?string $ignoreColumn = null,
        bool $withoutTrashed = false,
        string $deletedAtColumn = 'deleted_at',
        ?Closure $where = null,
        ?BaseUnique $rule = null
    ) {
        if ($table === null && $rule === null) {
            throw new Exception('Could not create unique validation rule, either table or a rule is required');
        }

        $rule ??= new BaseUnique(
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

        $this->rule = $rule;
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'unique';
    }

    public static function create(string ...$parameters): static
    {
        return new static(rule: new BaseUnique($parameters[0], $parameters[1]));
    }
}
