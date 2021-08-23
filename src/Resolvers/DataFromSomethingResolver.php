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
            return app(DataFromArrayResolver::class)->execute($class, $value->toArray());
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
            return $customCreationMethods[ltrim($payload::class, ' \\')] ?? null;
        }

        $type = match ($type) {
            'boolean' => 'bool',
            'string' => 'string',
            'integer' => 'int',
            'double' => 'float',
            'array' => 'array',
            // no break
            default => null,
        };

        return $customCreationMethods[$type] ?? null;
    }
}
