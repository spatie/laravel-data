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
    public function __construct(
        protected null|string|RouteParameterReference $table = null,
        protected null|string|RouteParameterReference $column = 'NULL',
        protected null|string|RouteParameterReference $connection = null,
        protected null|string|RouteParameterReference $ignore = null,
        protected null|string|RouteParameterReference $ignoreColumn = null,
        protected bool|RouteParameterReference        $withoutTrashed = false,
        protected string|RouteParameterReference      $deletedAtColumn = 'deleted_at',
        protected ?Closure                            $where = null,
        protected ?BaseUnique                         $rule = null
    ) {
        if ($table === null && $rule === null) {
            throw new Exception('Could not create unique validation rule, either table or a rule is required');
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
        $ignore = $this->normalizePossibleRouteReferenceParameter($this->ignore);
        $ignoreColumn = $this->normalizePossibleRouteReferenceParameter($this->ignoreColumn);
        $withoutTrashed = $this->normalizePossibleRouteReferenceParameter($this->withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleRouteReferenceParameter($this->deletedAtColumn);

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

        if ($this->where) {
            $rule->where($this->where);
        }

        return $this->rule = $rule;
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
