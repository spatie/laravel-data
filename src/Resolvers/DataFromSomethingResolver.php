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
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(
        string $class,
        mixed $value
    ): Data {
        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */
        if ($customCreationMethod = $this->resolveCustomCreationMethod($class, $value)) {
            return $class::$customCreationMethod($value);
        }

        if ($value instanceof Model) {
            return app(DataFromModelResolver::class)->execute($class, $value);
        }

        if ($value instanceof Request) {
            return app(DataFromArrayResolver::class)->execute($class, $value->all());
        }

        if ($value instanceof Arrayable) {
            return app(DataFromArrayResolver::class)->execute($class, $value->toArray());
        }

        if (is_array($value)) {
            return app(DataFromArrayResolver::class)->execute($class, $value);
        }

        throw CannotCreateDataFromValue::create($class, $value);
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
