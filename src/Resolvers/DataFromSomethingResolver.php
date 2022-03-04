<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\Normalizers\ArraybleNormalizer;
use Spatie\LaravelData\Pipes\AuthorizedPipe;
use Spatie\LaravelData\Pipes\ValidatePropertiesPipe;
use Spatie\LaravelData\Support\DataConfig;
use stdClass;

class DataFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromArrayResolver $dataFromArrayResolver,
    ) {
    }

    public function execute(string $class, mixed $value)
    {
        if ($customCreationMethod = $this->resolveCustomCreationMethod($class, $value)) {
            return $this->createDataFromCustomCreationMethod($class, $customCreationMethod, $value);
        }

        /** @var \Spatie\LaravelData\DataPipeline $pipeline */
        $pipeline = $class::pipeline();

        $piped = $pipeline->using($value)->execute();

        if ($piped instanceof Data) {
            return $piped;
        }

        return $this->dataFromArrayResolver->execute($class, $piped);
    }

    private function resolveCustomCreationMethod(string $class, mixed $payload): ?string
    {
        $customCreationMethods = $this->dataConfig->getDataClass($class)->creationMethods();

        $type = gettype($payload);

        if ($type === 'object') {
            return $this->resolveCustomCreationMethodForObject($customCreationMethods, $payload);
        }

        $type = match ($type) {
            'boolean' => 'bool',
            'string' => 'string',
            'integer' => 'int',
            'double' => 'float',
            'array' => 'array',
            default => null,
        };

        return $customCreationMethods[$type] ?? null;
    }

    private function resolveCustomCreationMethodForObject(array $customCreationMethods, mixed $payload): ?string
    {
        $className = ltrim($payload::class, ' \\');

        if (array_key_exists($className, $customCreationMethods)) {
            return $customCreationMethods[$className];
        }

        foreach ($customCreationMethods as $customCreationMethodType => $customCreationMethod) {
            if (is_a($className, $customCreationMethodType, true)) {
                return $customCreationMethod;
            }
        }

        return null;
    }

    private function createDataFromCustomCreationMethod(
        string $class,
        string $method,
        mixed $value,
    ): Data {
        if ($value instanceof Request) {
            DataPipeline::create()
                ->normalizer(ArraybleNormalizer::class)
                ->into($class)
                ->through(AuthorizedPipe::class)
                ->through(ValidatePropertiesPipe::class)
                ->using($value)
                ->execute();
        }

        return $class::$method($value);
    }
}
