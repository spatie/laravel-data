<?php

namespace Spatie\LaravelData\Serializers;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataFromArrayResolver;
use Spatie\LaravelData\Support\PropertiesMapper;

class ArraySerializer implements DataSerializer
{
    public function __construct(
        protected DataFromArrayResolver $dataFromArrayResolver,
        protected PropertiesMapper $propertiesMapper,
    ) {
    }

    public function serialize(string $class, mixed $payload): ?Data
    {
        $properties = match (true) {
            is_array($payload) => $payload,
            $payload instanceof Arrayable => $payload->toArray(),
            default => null,
        };

        if ($properties === null) {
            return null;
        }

        $properties = $this->propertiesMapper->execute($properties, $class);

        return $this->dataFromArrayResolver->execute($class, $properties);
    }
}
