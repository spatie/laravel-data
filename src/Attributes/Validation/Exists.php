<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Exception;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Exists extends ObjectValidationAttribute
{
    protected BaseExists $rule;

    public function __construct(
        null|string|RouteParameterReference $table = null,
        null|string|RouteParameterReference $column = 'NULL',
        null|string|RouteParameterReference $connection = null,
        bool|RouteParameterReference $withoutTrashed = false,
        string|RouteParameterReference $deletedAtColumn = 'deleted_at',
        ?Closure $where = null,
        ?BaseExists $rule = null,
    ) {
        $table = $this->normalizePossibleRouteReferenceParameter($table);
        $column = $this->normalizePossibleRouteReferenceParameter($column);
        $connection = $this->normalizePossibleRouteReferenceParameter($connection);
        $withoutTrashed = $this->normalizePossibleRouteReferenceParameter($withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleRouteReferenceParameter($deletedAtColumn);

        if ($rule === null && $table === null) {
            throw new Exception('Could not make exists rule since a table or rule is required');
        }

        $rule ??= new BaseExists(
            $connection ? "{$connection}.{$table}" : $table,
            $column
        );

        if ($withoutTrashed) {
            $rule->withoutTrashed($deletedAtColumn);
        }

        if ($where) {
            $rule->where($where);
        }

        $this->rule = $rule;
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
    }

    public static function keyword(): string
    {
        return 'exists';
    }

    public static function create(string ...$parameters): static
    {
        return new static(rule: new BaseExists($parameters[0], $parameters[1] ?? 'NULL'));
    }
}
