<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateDataFromValue;
use Spatie\LaravelData\Support\DataConfig;

class DataFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataValidatorResolver $dataValidatorResolver,
        protected DataFromModelResolver $dataFromModelResolver,
        protected DataFromArrayResolver $dataFromArrayResolver,
    ) {
    }

    public function execute(string $class, mixed $value): Data
    {
        if ($value instanceof Request) {
            $this->ensureRequestIsValid($class, $value);
        }

        /** @var class-string<\Spatie\LaravelData\Data>|\Spatie\LaravelData\Data $class */
        if ($customCreationMethod = $this->resolveCustomCreationMethod($class, $value)) {
            return $class::$customCreationMethod($value);
        }

        if ($value instanceof Model) {
            return $this->dataFromModelResolver->execute($class, $value);
        }

        if ($value instanceof Request) {
            return $this->dataFromArrayResolver->execute($class, $value->all());
        }

        if ($value instanceof Arrayable) {
            return $this->dataFromArrayResolver->execute($class, $value->toArray());
        }

        if (is_array($value)) {
            return $this->dataFromArrayResolver->execute($class, $value);
        }

        throw CannotCreateDataFromValue::create($class, $value);
    }

    private function ensureRequestIsValid(string $class, Request $value): void
    {
        /** @var \Spatie\LaravelData\Data|string $class */
        if ($this->dataConfig->getDataClass($class)->hasAuthorizationMethod()) {
            $this->ensureRequestIsAuthorized($class);
        }

        $this->dataValidatorResolver->execute($class, $value)->validate();
    }

    private function ensureRequestIsAuthorized(string $class): void
    {
        /** @psalm-suppress UndefinedMethod */
        if ($class::authorized() === false) {
            throw new AuthorizationException();
        }
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
