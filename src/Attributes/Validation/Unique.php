<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Exception;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Unique extends ObjectValidationAttribute
{
    protected BaseUnique $rule;

    public function __construct(
        null|string|RouteParameterReference $table = null,
        null|string|RouteParameterReference $column = 'NULL',
        null|string|RouteParameterReference $connection = null,
        null|string|RouteParameterReference $ignore = null,
        null|string|RouteParameterReference $ignoreColumn = null,
        bool|RouteParameterReference        $withoutTrashed = false,
        string|RouteParameterReference      $deletedAtColumn = 'deleted_at',
        ?Closure                            $where = null,
        ?BaseUnique                         $rule = null
    ) {
        $table = $this->normalizePossibleRouteReferenceParameter($table);
        $column = $this->normalizePossibleRouteReferenceParameter($column);
        $connection = $this->normalizePossibleRouteReferenceParameter($connection);
        $ignore = $this->normalizePossibleRouteReferenceParameter($ignore);
        $ignoreColumn = $this->normalizePossibleRouteReferenceParameter($ignoreColumn);
        $withoutTrashed = $this->normalizePossibleRouteReferenceParameter($withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleRouteReferenceParameter($deletedAtColumn);

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

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
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
