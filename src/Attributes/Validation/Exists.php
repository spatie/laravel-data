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
    public function __construct(
        protected null|string|RouteParameterReference $table = null,
        protected null|string|RouteParameterReference $column = 'NULL',
        protected null|string|RouteParameterReference $connection = null,
        protected bool|RouteParameterReference $withoutTrashed = false,
        protected string|RouteParameterReference $deletedAtColumn = 'deleted_at',
        protected ?Closure $where = null,
        protected ?BaseExists $rule = null,
    ) {
        if ($rule === null && $table === null) {
            throw new Exception('Could not make exists rule since a table or rule is required');
        }
    }

    public function getRule(ValidationPath $path): object|string
    {
        if($this->rule) {
            return $this->rule;
        }

        $table = $this->normalizePossibleRouteReferenceParameter($this->table);
        $column = $this->normalizePossibleRouteReferenceParameter($this->column);
        $connection = $this->normalizePossibleRouteReferenceParameter($this->connection);
        $withoutTrashed = $this->normalizePossibleRouteReferenceParameter($this->withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleRouteReferenceParameter($this->deletedAtColumn);

        $rule = new BaseExists(
            $connection ? "{$connection}.{$table}" : $table,
            $column
        );

        if ($withoutTrashed) {
            $rule->withoutTrashed($deletedAtColumn);
        }

        if ($this->where) {
            $rule->where($this->where);
        }

        return $this->rule = $rule;
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
