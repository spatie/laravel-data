<?php

namespace Spatie\LaravelData\Tests\Factories;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Factories\DataClassFactory;
use Spatie\LaravelData\Support\Factories\DataMethodFactory;
use Spatie\LaravelData\Support\Factories\DataParameterFactory;
use Spatie\LaravelData\Support\Factories\DataPropertyFactory;
use Spatie\LaravelData\Support\Factories\DataReturnTypeFactory;
use Spatie\LaravelData\Support\Factories\DataTypeFactory;

class FakeDataStructureFactory
{
    protected static DataClassFactory $dataClassFactory;

    protected static DataMethodFactory $methodFactory;

    protected static DataPropertyFactory $propertyFactory;
    protected static DataParameterFactory $parameterFactory;

    protected static DataTypeFactory $typeFactory;

    protected static DataReturnTypeFactory $returnTypeFactory;

    public static function class(
        object|string $class,
    ): DataClass {
        if (! $class instanceof ReflectionClass) {
            $class = new ReflectionClass($class);
        }

        $factory = static::$dataClassFactory ??= app(DataClassFactory::class);

        return $factory->build($class);
    }

    public static function method(
        ReflectionMethod $method,
    ): DataMethod {
        $factory = static::$methodFactory ??= app(DataMethodFactory::class);

        return $factory->build($method, $method->getDeclaringClass());
    }

    public static function constructor(
        ReflectionMethod $method,
        Collection $properties
    ): DataMethod {
        $factory = static::$methodFactory ??= app(DataMethodFactory::class);

        return $factory->buildConstructor($method, $method->getDeclaringClass(), $properties);
    }

    public static function property(
        object $class,
        string $name,
    ): DataProperty {
        $reflectionClass = new ReflectionClass($class);
        $reflectionProperty = new ReflectionProperty($class, $name);

        $factory = static::$propertyFactory ??= app(DataPropertyFactory::class);

        return $factory->build($reflectionProperty, $reflectionClass);
    }

    public static function parameter(
        ReflectionParameter $parameter,
    ): DataParameter {
        $factory = static::$parameterFactory ??= app(DataParameterFactory::class);

        return $factory->build($parameter, $parameter->getDeclaringClass());
    }
}
