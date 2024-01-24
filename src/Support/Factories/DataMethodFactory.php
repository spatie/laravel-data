<?php

namespace Spatie\LaravelData\Support\Factories;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataType;

class DataMethodFactory
{
    public function __construct(
        protected DataParameterFactory $parameterFactory,
        protected DataTypeFactory $typeFactory,
        protected DataReturnTypeFactory $returnTypeFactory,
    ) {
    }

    public function build(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass,
    ): DataMethod {
        $returnType = $reflectionMethod->getReturnType()
            ? $this->returnTypeFactory->build($reflectionMethod, $reflectionClass)
            : null;

        return new DataMethod(
            name: $reflectionMethod->name,
            parameters: collect($reflectionMethod->getParameters())->map(
                fn (ReflectionParameter $parameter) => $this->parameterFactory->build($parameter, $reflectionClass),
            ),
            isStatic: $reflectionMethod->isStatic(),
            isPublic: $reflectionMethod->isPublic(),
            customCreationMethodType: $this->resolveCustomCreationMethodType($reflectionMethod, $returnType),
            returnType: $returnType
        );
    }

    public function buildConstructor(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $reflectionClass,
        Collection $properties
    ): DataMethod {
        $parameters = collect($reflectionMethod->getParameters())
            ->map(function (ReflectionParameter $parameter) use ($reflectionClass, $properties) {
                if (! $parameter->isPromoted()) {
                    return $this->parameterFactory->build($parameter, $reflectionClass);
                }

                if ($properties->has($parameter->name)) {
                    return $properties->get($parameter->name);
                }

                return null;
            })
            ->filter()
            ->values();

        return new DataMethod(
            name: '__construct',
            parameters: $parameters,
            isStatic: false,
            isPublic: $reflectionMethod->isPublic(),
            customCreationMethodType: CustomCreationMethodType::None,
            returnType: null,
        );
    }

    protected function resolveCustomCreationMethodType(
        ReflectionMethod $method,
        ?DataType $returnType,
    ): CustomCreationMethodType {
        if (str_starts_with($method->name, 'from')) {
            return CustomCreationMethodType::Object;
        }

        if (str_starts_with($method->name, 'collect') && $returnType) {
            return CustomCreationMethodType::Collection;
        }

        return CustomCreationMethodType::None;
    }
}
