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
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, mixed $value): Data
    {
        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */

        if ($customFromMethod = $this->resolveCustomFromMethod($class, $value)) {
            return $class::$customFromMethod($value);
        }

        if ($value instanceof Model) {
            return $class::fromModel($value);
        }

        if ($value instanceof Request) {
            return $class::fromRequest($value);
        }

        if ($value instanceof Arrayable) {
            return $class::fromArray($value->toArray());
        }

        if (is_array($value)) {
            return $class::fromArray($value);
        }

        throw CannotCreateDataFromValue::create($class, $value);
    }

    private function resolveCustomFromMethod(string $class, mixed $payload): ?string
    {
        $customFromMethods = $this->dataConfig->getDataClass($class)->customFromMethods();

        $type = gettype($payload);

        if ($type === 'object') {
            return $customFromMethods[ltrim($payload::class, ' \\')] ?? null;
        }

        $type = match ($type) {
            'boolean' => 'bool',
            'string' => 'string',
            'integer' => 'int',
            'double' => 'float',
            'array' => 'array',
            default => null,
        };

        return $customFromMethods[$type] ?? null;
    }
}
