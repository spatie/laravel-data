<?php

namespace Spatie\LaravelData\Resolvers;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;

class DataFromSomethingResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, mixed $payload): Data
    {
        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */

        if ($customFromMethod = $this->resolveCustomFromMethod($class, $payload)) {
            return $class::$customFromMethod($payload);
        }

        if ($payload instanceof Model) {
            return $class::fromModel($payload);
        }

        if ($payload instanceof Request) {
            return $class::fromRequest($payload);
        }

        if ($payload instanceof Arrayable) {
            return $class::fromArray($payload->toArray());
        }

        if (is_array($payload)) {
            return $class::fromArray($payload);
        }

        throw new Exception("Could not create a data object from value");
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
