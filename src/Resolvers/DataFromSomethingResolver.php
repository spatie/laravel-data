<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateDataFromValue;

class DataFromSomethingResolver
{
    public function execute(string $class, mixed $value): Data
    {
        foreach ($class::serializers() as $serializerClass) {
            /** @var \Spatie\LaravelData\Serializers\DataSerializer $serializer */
            $serializer = resolve($serializerClass);

            if ($data = $serializer->serialize($class, $value)) {
                return $data;
            }
        }

        throw CannotCreateDataFromValue::create($class, $value);
    }
}
