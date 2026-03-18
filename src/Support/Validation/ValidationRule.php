<?php

namespace Spatie\LaravelData\Support\Validation;

abstract class ValidationRule
{
    /**
     * The validation context(s) this rule applies to.
     * When null, the rule applies to all contexts.
     *
     * @var array<string>|string|null
     */
    public array|string|null $context = null;

    /**
     * Determine if this rule applies to the given context.
     *
     * @param  string|null  $currentContext  The current validation context name
     */
    public function appliesToContext(?string $currentContext): bool
    {
        // If no context is defined on the rule, it applies to all contexts (backward compatible)
        if ($this->context === null) {
            return true;
        }

        // If no current context is set, don't apply context-specific rules
        if ($currentContext === null) {
            return false;
        }

        $contexts = is_array($this->context)
            ? $this->context
            : [$this->context];

        return in_array($currentContext, $contexts, true);
    }

    /**
     * Extract context from variadic arguments that may contain a named 'context' parameter.
     *
     * @param  array<int|string, mixed>  $values  The variadic arguments
     * @return array{values: array<int, mixed>, context: array|string|null}
     */
    protected function extractContextFromVariadicValues(array $values): array
    {
        $context = null;
        $filteredValues = [];

        foreach ($values as $key => $value) {
            if ($key === 'context') {
                $context = $value;
            } else {
                $filteredValues[] = $value;
            }
        }

        return [
            'values' => $filteredValues,
            'context' => $context,
        ];
    }
}
