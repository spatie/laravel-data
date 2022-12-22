<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\FromRouteModel;
use Spatie\LaravelData\Support\DataClass;

class FillRouteModelPropertiesDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            if (! ($attribute = $dataProperty->attributes->first(fn ($attribute) => $attribute instanceof FromRouteModel))) {
                continue;
            }

            if (! $attribute->replace && $properties->has($dataProperty->name)) {
                continue;
            }

            if ($model = $payload->route($attribute->routeModel)) {
                $routeModelProperty = $attribute->routeModelProperty ?? $dataProperty->name;
                $properties->put($dataProperty->name, $model->{$routeModelProperty});
            }
        }

        return $properties;
    }
}
