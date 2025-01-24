<?php

namespace Spatie\LaravelData\Attributes\Concerns;

use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

trait ResolvesPropertyForInjectedValue
{
    abstract protected function getPropertyKey(): string|null;

    public function resolvePropertyForInjectedValue(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        $injected = parent::resolve(
            $dataProperty,
            $payload,
            $properties,
            $creationContext
        );

        if ($injected === Skipped::create()) {
            return $injected;
        }

        if (is_scalar($injected)) {
            throw CannotFillFromRouteParameterPropertyUsingScalarValue::create($dataProperty, $this);
        }

        return data_get($injected, $this->getPropertyKey() ?? $dataProperty->name);
    }
}
