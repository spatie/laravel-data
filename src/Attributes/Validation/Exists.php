<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Exception;
use Illuminate\Validation\Rules\Exists as BaseExists;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Exists extends ObjectValidationAttribute
{
    public function __construct(
        protected null|string|ExternalReference $table = null,
        protected null|string|ExternalReference $column = 'NULL',
        protected null|string|ExternalReference $connection = null,
        protected bool|ExternalReference $withoutTrashed = false,
        protected string|ExternalReference $deletedAtColumn = 'deleted_at',
        protected ?Closure $where = null,
        protected ?BaseExists $rule = null,
    ) {
        if ($rule === null && $table === null) {
            throw new Exception('Could not make exists rule since a table or rule is required');
        }
    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        $table = $this->normalizePossibleExternalReferenceParameter($this->table);
        $column = $this->normalizePossibleExternalReferenceParameter($this->column);
        $connection = $this->normalizePossibleExternalReferenceParameter($this->connection);
        $withoutTrashed = $this->normalizePossibleExternalReferenceParameter($this->withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleExternalReferenceParameter($this->deletedAtColumn);

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

        return $rule;
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
