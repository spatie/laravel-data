<?php

namespace Spatie\LaravelData\Attributes\Concerns;

use Closure;
use InvalidArgumentException;
use Spatie\LaravelData\Support\Validation\Constraints\DatabaseConstraint;

trait AppliesDatabaseConstraints
{
    /** @param Closure|DatabaseConstraint|array<int, DatabaseConstraint|Closure> $constraints */
    protected function applyDatabaseConstraints(object $rule, Closure|DatabaseConstraint|array $constraints): void
    {
        $constraintsList = is_array($constraints) ? $constraints : [$constraints];

        foreach ($constraintsList as $constraint) {
            match (true) {
                $constraint instanceof Closure => $rule->where($constraint),
                $constraint instanceof DatabaseConstraint => $constraint->apply($rule),
                default => throw new InvalidArgumentException('Each where item must be a DatabaseConstraint or Closure'),
            };
        }
    }
}
