<?php

namespace Spatie\LaravelData\DataSerializers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

class MagicMethodSerializer implements DataSerializer
{
    public function __construct(protected DataClass $dataClass)
    {
    }

    public function serialize(mixed $payload): array|Data|null
    {
        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */
        if ($customCreationMethod = $this->resolveCustomCreationMethod($payload)) {
            return ($this->dataClass->name())::$customCreationMethod($payload);
        }

        return null;
    }

    protected function resolveCustomCreationMethod(mixed $payload): ?string
    {
        $customCreationMethods = $this->dataClass->creationMethods();

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

    protected function resolveCustomCreationMethodForObject(array $customCreationMethods, mixed $payload): ?string
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
