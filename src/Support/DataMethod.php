<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Support\Types\Type;
use Spatie\LaravelData\Support\Types\UndefinedType;

/**
 * @property Collection<DataParameter> $parameters
 */
class DataMethod
{
    public function __construct(
        public readonly string $name,
        public readonly Collection $parameters,
        public readonly bool $isStatic,
        public readonly bool $isPublic,
        public readonly CustomCreationMethodType $customCreationMethodType,
        public readonly Type $returnType,
    ) {
    }

    public static function create(ReflectionMethod $method): self
    {
        $returnType = Type::forReflection(
            $method->getReturnType(),
            $method->class,
        );

        return new self(
            $method->name,
            collect($method->getParameters())->map(
                fn (ReflectionParameter $parameter) => DataParameter::create($parameter, $method->class),
            ),
            $method->isStatic(),
            $method->isPublic(),
            self::resolveCustomCreationMethodType($method, $returnType),
            $returnType
        );
    }

    public static function createConstructor(?ReflectionMethod $method, Collection $properties): ?self
    {
        if ($method === null) {
            return null;
        }

        $parameters = collect($method->getParameters())
            ->map(function (ReflectionParameter $parameter) use ($method, $properties) {
                if (! $parameter->isPromoted()) {
                    return DataParameter::create($parameter, $method->class);
                }

                if ($properties->has($parameter->name)) {
                    return $properties->get($parameter->name);
                }

                return null;
            })
            ->filter()
            ->values();

        return new self(
            '__construct',
            $parameters,
            false,
            $method->isPublic(),
            CustomCreationMethodType::None,
            new UndefinedType(),
        );
    }

    protected static function resolveCustomCreationMethodType(
        ReflectionMethod $method,
        ?Type $returnType,
    ): CustomCreationMethodType {
        if (! $method->isStatic()
            || ! $method->isPublic()
            || $method->name === 'from'
            || $method->name === 'collect'
            || $method->name === 'collection'
        ) {
            return CustomCreationMethodType::None;
        }

        if (str_starts_with($method->name, 'from')) {
            return CustomCreationMethodType::Object;
        }

        if (str_starts_with($method->name, 'collect') && ! $returnType instanceof UndefinedType) {
            return CustomCreationMethodType::Collection;
        }

        return CustomCreationMethodType::None;
    }

    public function accepts(mixed ...$input): bool
    {
        /** @var Collection<array-key, \Spatie\LaravelData\Support\DataParameter|\Spatie\LaravelData\Support\DataProperty> $parameters */
        $parameters = array_is_list($input)
            ? $this->parameters
            : $this->parameters->mapWithKeys(fn (DataParameter|DataProperty $parameter) => [$parameter->name => $parameter]);

        $parameters = $parameters->reject(
            fn (DataParameter|DataProperty $parameter) => $parameter instanceof DataParameter &&  $parameter->isCreationContext
        );

        if (count($input) > $parameters->count()) {
            return false;
        }

        foreach ($parameters as $index => $parameter) {
            $parameterProvided = array_key_exists($index, $input);

            if (! $parameterProvided && $parameter->hasDefaultValue === false) {
                return false;
            }

            if (! $parameterProvided && $parameter->hasDefaultValue) {
                continue;
            }

            if (
                $parameter instanceof DataProperty
                && ! $parameter->type->type->acceptsValue($input[$index])
            ) {
                return false;
            }

            if (
                $parameter instanceof DataParameter
                && ! $parameter->type->acceptsValue($input[$index])
            ) {
                return false;
            }
        }

        return true;
    }

    public function returns(string $type): bool
    {
        return $this->returnType->acceptsType($type);
    }
}
