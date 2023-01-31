<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Support\DataClass;

class FillRouteParameterPropertiesDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            if (! ($attribute = $dataProperty->attributes->first(fn ($attribute) => $attribute instanceof FromRouteParameter))) {
                continue;
            }

            if (! $attribute->replaceWhenPresentInBody && $properties->has($dataProperty->name)) {
                continue;
            }

            if (($model = $payload->route($attribute->routeParameter))) {
                $properties->put(
                    $dataProperty->name,
                    data_get($model, $attribute->property ?? $dataProperty->name)
                );
            }
        }

        return $properties;
    }
}
