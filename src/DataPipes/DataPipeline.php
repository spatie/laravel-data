<?php

namespace Spatie\LaravelData\DataPipes;

use Exception;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataSerializers\DataSerializer;
use Spatie\LaravelData\Exceptions\CannotCreateDataFromValue;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataPipeline
{
    /** @var \Spatie\LaravelData\DataPipes\DataPipe[] */
    private array $pipes = [];

    /** @var array<DataSerializer|class-string<DataSerializer>> */
    private array $serializers = [];

    private string $dataClass;

    public static function create(): static
    {
        return new static();
    }

    public function __construct()
    {
    }

    public function from(mixed $initialPayload): Data
    {
        $dataClass = app(DataConfig::class)->getDataClass($this->dataClass);

        $payload = $this->serializePayload($initialPayload, $dataClass);

        if($payload instanceof Data){
            // TODO: this will cause no pipeline execution, so no validation or authorisation on magic methods
            return $payload;
        }

        $payload = collect($payload);

        foreach ($this->pipes as $pipe) {
            $payload = $pipe->execute($initialPayload, $payload, $dataClass);
        }

        [$promotedProperties, $classProperties] = $dataClass->properties()->partition(
            fn(DataProperty $property) => $property->isPromoted()
        );

        return $this->createDataObjectWithProperties(
            $this->dataClass,
            $promotedProperties->mapWithKeys(fn(DataProperty $property) => [
                $property->name() => $payload->get($property->name()),
            ]),
            $classProperties->mapWithKeys(fn(DataProperty $property) => [
                $property->name() => $payload->get($property->name()),
            ])
        );
    }

    public function pipe(DataPipe|string $pipe): static
    {
        $this->pipes[] = is_string($pipe) ? resolve($pipe) : $pipe;

        return $this;
    }

    public function serializer(DataSerializer|string $serializer): static
    {
        $this->serializers[] = $serializer;

        return $this;
    }

    public function into(string $dataClass): static
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    private function serializePayload(
        mixed $initialPayload,
        DataClass $dataClass,
    ): array|Data {
        foreach ($this->serializers as $serializer) {
            $serializer = $serializer instanceof DataSerializer
                ? $serializer
                : resolve($serializer, ['dataClass' => $dataClass]);

            $serialized = $serializer->serialize($initialPayload);

            if ($serialized !== null) {
                return $serialized;
            }
        }

        throw CannotCreateDataFromValue::create($dataClass->name(), $initialPayload);
    }

    private function createDataObjectWithProperties(
        string $class,
        Collection $promotedProperties,
        Collection $classProperties
    ): Data {
        $data = new $class(...$promotedProperties);

        $classProperties->each(
            function (mixed $value, string $name) use ($data) {
                $data->{$name} = $value;
            }
        );

        return $data;
    }
}
