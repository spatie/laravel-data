<?php

namespace Spatie\LaravelData\Normalizers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof Model) {
            return null;
        }

        $properties = $value->toArray();

        foreach ($value->getDates() as $key) {
            if (isset($properties[$key])) {
                $properties[$key] = $value->getAttribute($key);
            }
        }

        foreach ($value->getCasts() as $key => $cast) {
            if ($this->isDateCast($cast)) {
                if (isset($properties[$key])) {
                    $properties[$key] = $value->getAttribute($key);
                }
            }
        }

        foreach ($value->getRelations() as $key => $relation) {
            $key = $value::$snakeAttributes ? Str::snake($key) : $key;

            if (isset($properties[$key])) {
                $properties[$key] = $relation;
            }
        }

        foreach ($value->getMutatedAttributes() as $key) {
            $properties[$key] = $value->getAttribute($key);
        }

        return $properties;
    }

    protected function isDateCast(string $cast): bool
    {
        return in_array($cast, [
            'date',
            'datetime',
            'immutable_date',
            'immutable_datetime',
            'custom_datetime',
            'immutable_custom_datetime',
        ]);
    }
}
