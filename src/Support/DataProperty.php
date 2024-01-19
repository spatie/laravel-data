<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Mappers\NameMapper;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotation;
use Spatie\LaravelData\Support\Factories\DataTypeFactory;
use Spatie\LaravelData\Support\Factories\OldDataTypeFactory;
use Spatie\LaravelData\Transformers\Transformer;

/**
 * @property Collection<string, object> $attributes
 */
class DataProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $className,
        public readonly DataType $type,
        public readonly bool $validate,
        public readonly bool $computed,
        public readonly bool $hidden,
        public readonly bool $isPromoted,
        public readonly bool $isReadonly,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly ?Cast $cast,
        public readonly ?Transformer $transformer,
        public readonly ?string $inputMappedName,
        public readonly ?string $outputMappedName,
        public readonly Collection $attributes,
    ) {
    }
}
