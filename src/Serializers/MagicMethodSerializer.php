<?php

namespace Spatie\LaravelData\Serializers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;

class MagicMethodSerializer implements DataSerializer
{
    public function __construct(protected  DataConfig $dataConfig,)
    {
    }

    public function serialize(string $class, mixed $payload): ?Data
    {
        if ($customCreationMethod = $this->resolveCustomCreationMethod($class, $payload)) {
            return $class::$customCreationMethod($payload);
        }

        return null;
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
}
