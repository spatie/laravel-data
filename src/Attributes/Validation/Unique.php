<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Closure;
use Exception;
use Illuminate\Validation\Rules\Unique as BaseUnique;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Unique extends ObjectValidationAttribute
{
    public function __construct(
        protected null|string|ExternalReference $table = null,
        protected null|string|ExternalReference $column = 'NULL',
        protected null|string|ExternalReference $connection = null,
        protected null|string|ExternalReference $ignore = null,
        protected null|string|ExternalReference $ignoreColumn = null,
        protected bool|ExternalReference $withoutTrashed = false,
        protected string|ExternalReference $deletedAtColumn = 'deleted_at',
        protected ?Closure $where = null,
        protected ?BaseUnique $rule = null
    ) {
        if ($table === null && $rule === null) {
            throw new Exception('Could not create unique validation rule, either table or a rule is required');
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
        $ignore = $this->normalizePossibleExternalReferenceParameter($this->ignore);
        $ignoreColumn = $this->normalizePossibleExternalReferenceParameter($this->ignoreColumn);
        $withoutTrashed = $this->normalizePossibleExternalReferenceParameter($this->withoutTrashed);
        $deletedAtColumn = $this->normalizePossibleExternalReferenceParameter($this->deletedAtColumn);

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

        return $rule;
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
