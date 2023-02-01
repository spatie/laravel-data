<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;
use Spatie\LaravelData\Support\DataClass;

class FillRouteParameterPropertiesDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            if (! ($attribute = $dataProperty->attributes->first(fn ($attribute) => $attribute instanceof FromRouteParameter || $attribute instanceof FromRouteParameterProperty))) {
                continue;
            }

            if (! $attribute->replaceWhenPresentInBody && $properties->has($dataProperty->name)) {
                continue;
            }

            if (! ($parameter = $payload->route($attribute->routeParameter))) {
                continue;
            }

            if ($attribute instanceof FromRouteParameterProperty) {
                if (is_scalar($parameter)) {
                    throw CannotFillFromRouteParameterPropertyUsingScalarValue::create($dataProperty, $attribute, $parameter);
                }

                $value = data_get($parameter, $attribute->property ?? $dataProperty->name);
            } else {
                $value = $parameter;
            }

            $properties->put($dataProperty->name, $value);
        }

        return $properties;
    }
}
