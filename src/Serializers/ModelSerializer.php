<?php

namespace Spatie\LaravelData\Serializers;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;

class ModelSerializer implements DataSerializer
{
    public function __construct(
        private DataFromArrayResolver $dataFromArrayResolver
    ) {
    }

    public function serialize(string $class, mixed $payload): ?Data
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

        return $this->dataFromArrayResolver->execute(
            $class,
            $values
        );
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
