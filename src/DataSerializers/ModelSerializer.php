<?php

namespace Spatie\LaravelData\DataSerializers;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class ModelSerializer implements DataSerializer
{
    public function serialize(mixed $payload): array|Data|null
    {
        if (! $payload instanceof Model) {
            return null;
        }

        $values = $payload->toArray();

        foreach ($payload->getDates() as $key) {
            $values[$key] = $payload->getAttribute($key);
        }

        foreach ($payload->getCasts() as $key => $cast) {
            if ($this->isDateCast($cast)) {
                $values[$key] = $payload->getAttribute($key);
            }
        }

        return $values;
    }

    private function isDateCast(string $cast): bool
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
