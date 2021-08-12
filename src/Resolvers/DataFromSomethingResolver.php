<?php

namespace Spatie\LaravelData\Resolvers;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateDataFromValue;
use Spatie\LaravelData\Support\DataConfig;

class DataFromSomethingResolver
{
    public const TYPE_OPTIONAL = 'optional';
    public const TYPE_FROM = 'from';

    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(
        string $class,
        string $type,
        mixed $value
    ): ?Data {
        if ($type === static::TYPE_OPTIONAL && $value === null) {
            return null;
        }

        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */
        if ($customCreationMethod = $this->resolveCustomCreationMethod($class, $type, $value)) {
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

    private function resolveCustomCreationMethod(string $class, string $type, mixed $payload): ?string
    {
        $customCreationMethods = match ($type){
            self::TYPE_FROM => $this->dataConfig->getDataClass($class)->creationMethods(),
            self::TYPE_OPTIONAL => $this->dataConfig->getDataClass($class)->optionalCreationMethods(),
            default => throw new Exception('Unknown creation type')
        };

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
            default => null,
        };

        return $customCreationMethods[$type] ?? null;
    }
}
