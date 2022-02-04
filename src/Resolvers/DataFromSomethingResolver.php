<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateDataFromValue;
use Spatie\LaravelData\Support\DataConfig;

class DataFromSomethingResolver
{
    public function execute(string $class, mixed $value): Data
    {
        foreach ($class::serializers() as $serializerClass) {
            /** @var \Spatie\LaravelData\Serializers\DataSerializer $serializer */
            $serializer = resolve($serializerClass);

            if($data = $serializer->serialize($class, $value)){
                return $data;
            }
        }

        throw CannotCreateDataFromValue::create($class, $value);
    }
}
