<?php

namespace Spatie\LaravelData\Attributes\Concerns;

use Closure;
use InvalidArgumentException;
use Spatie\LaravelData\Support\Validation\Constraints\DatabaseConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereInConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereNotConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereNotInConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereNotNullConstraint;
use Spatie\LaravelData\Support\Validation\Constraints\WhereNullConstraint;

trait AppliesDatabaseConstraints
{
    /**
     * @param object $rule A validation rule that uses the DatabaseRule trait (e.g., Unique, Exists)
     */
    protected function applyDatabaseConstraints(object $rule, Closure|DatabaseConstraint|array $constraints): void
    {
        $constraintsList = is_array($constraints) ? $constraints : [$constraints];

        foreach ($constraintsList as $constraint) {
            match (true) {
                $constraint instanceof Closure => $rule->where($constraint),
                $constraint instanceof WhereConstraint => $rule->where(...$constraint->toArray()),
                $constraint instanceof WhereNotConstraint => $rule->whereNot(...$constraint->toArray()),
                $constraint instanceof WhereNullConstraint => $rule->whereNull(...$constraint->toArray()),
                $constraint instanceof WhereNotNullConstraint => $rule->whereNotNull(...$constraint->toArray()),
                $constraint instanceof WhereInConstraint => $rule->whereIn(...$constraint->toArray()),
                $constraint instanceof WhereNotInConstraint => $rule->whereNotIn(...$constraint->toArray()),
                default => throw new InvalidArgumentException('Each where item must be a DatabaseConstraint or Closure'),
            };
        }
    }
}
