<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

            if (! $attribute->replaceWhenPresentInBody && $properties->has($dataProperty->name)) {
                continue;
            }

            if (($model = $payload->route($attribute->routeParameter)) instanceof Model) {
                $routeModelProperty = $attribute->modelProperty ?? $dataProperty->name;
                $properties->put($dataProperty->name, Arr::get($model->toArray(), $routeModelProperty));
            }
        }

        return $properties;
    }
}
