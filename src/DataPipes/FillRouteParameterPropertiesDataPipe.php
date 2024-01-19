<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

class FillRouteParameterPropertiesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        if (! $payload instanceof Request) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            $attribute = $dataProperty->attributes->first(
                fn (object $attribute) => $attribute instanceof FromRouteParameter || $attribute instanceof FromRouteParameterProperty
            );

            if ($attribute === null) {
                continue;
            }

            if (! $attribute->replaceWhenPresentInBody && array_key_exists($dataProperty->name, $properties)) {
                continue;
            }

            $parameter = $payload->route($attribute->routeParameter);

            if ($parameter === null) {
                continue;
            }

            $properties[$dataProperty->name] = $this->resolveValue($dataProperty, $attribute, $parameter);
        }

        return $properties;
    }

    protected function resolveValue(
        DataProperty $dataProperty,
        FromRouteParameter|FromRouteParameterProperty $attribute,
        mixed $parameter,
    ): mixed {
        if ($attribute instanceof FromRouteParameter) {
            return $parameter;
        }

        if (is_scalar($parameter)) {
            throw CannotFillFromRouteParameterPropertyUsingScalarValue::create($dataProperty, $attribute, $parameter);
        }

        return data_get($parameter, $attribute->property ?? $dataProperty->name);
    }
}
