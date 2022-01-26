<?php

namespace Spatie\LaravelData\Support;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataTypeForProperty;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Undefined;
use TypeError;

class DataProperty
{
    protected bool $isLazy;

    protected bool $isNullable;

    protected bool $isUndefinable;

    protected bool $isData;

    protected bool $isDataCollection;

    protected string $dataClassName;

    protected DataPropertyTypes $types;

    /** @var \Spatie\LaravelData\Attributes\Validation\ValidationAttribute[] */
    protected array $validationAttributes;

    protected bool $withValidation;

    protected ?WithCast $castAttribute;

    protected ?WithTransformer $transformerAttribute;

    protected ?DataCollectionOf $dataCollectionOfAttribute;

    public static function create(
        ReflectionProperty $property,
        bool $hasDefaultValue = false,
        mixed $defaultValue = null
    ): static {
        return new self($property, $hasDefaultValue, $defaultValue);
    }

    public function __construct(
        protected ReflectionProperty $property,
        protected bool $hasDefaultValue = false,
        protected mixed $defaultValue = null
    ) {
        $type = $this->property->getType();

        match (true) {
            $type === null => $this->processNoType(),
            $type instanceof ReflectionNamedType => $this->processNamedType($type),
            $type instanceof ReflectionUnionType, $type instanceof ReflectionIntersectionType => $this->processListType($type),
            default => throw new TypeError(),
        };

        $this->ensurePropertyIsValid();
    }

    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function isUndefinable(): bool
    {
        return $this->isUndefinable;
    }

    public function isPromoted(): bool
    {
        return $this->property->isPromoted();
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function isData(): bool
    {
        return $this->isData;
    }

    public function isDataCollection(): bool
    {
        return $this->isDataCollection;
    }

    public function types(): DataPropertyTypes
    {
        return $this->types;
    }

    public function name(): string
    {
        return $this->property->getName();
    }

    public function className(): string
    {
        return $this->property->getDeclaringClass()->getName();
    }

    public function validationAttributes(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (! isset($this->validationAttributes)) {
            $this->loadAttributes();
        }

        return $this->validationAttributes;
    }

    public function shouldValidateProperty(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (! isset($this->withValidation)) {
            $this->loadAttributes();
        }

        return $this->withValidation;
    }

    public function castAttribute(): ?WithCast
    {
        if (! isset($this->castAttribute)) {
            $this->loadAttributes();
        }

        return $this->castAttribute;
    }

    public function transformerAttribute(): ?WithTransformer
    {
        if (! isset($this->transformerAttribute)) {
            $this->loadAttributes();
        }

        return $this->transformerAttribute;
    }

    public function dataCollectionOfAttribute(): ?DataCollectionOf
    {
        if (! isset($this->dataCollectionOfAttribute)) {
            $this->loadAttributes();
        }

        return $this->dataCollectionOfAttribute;
    }

    /**
     * @return class-string<\Spatie\LaravelData\Data>
     */
    public function dataClassName(): string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->dataClassName)) {
            return $this->dataClassName;
        }

        if ($this->isData) {
            return $this->dataClassName = $this->types->first();
        }

        if ($this->isDataCollection) {
            return $this->dataClassName = $this->resolveDataCollectionClass();
        }

        throw CannotFindDataTypeForProperty::noDataReferenceFound($this->className(), $this->name());
    }

    private function processNoType(): void
    {
        $this->isLazy = false;
        $this->isNullable = true;
        $this->isUndefinable = false;
        $this->isData = false;
        $this->isDataCollection = false;
        $this->types = new DataPropertyTypes();
    }

    private function processNamedType(ReflectionNamedType $type)
    {
        $name = $type->getName();

        if (is_a($name, Lazy::class, true)) {
            throw InvalidDataPropertyType::onlyLazy($this->property);
        }

        $this->isLazy = false;
        $this->isData = is_a($name, Data::class, true);
        $this->isDataCollection = is_a($name, DataCollection::class, true);
        $this->isNullable = $type->allowsNull();
        $this->isUndefinable = is_a($name, Undefined::class, true);
        $this->types = new DataPropertyTypes([$name]);
    }

    private function processListType(ReflectionUnionType|ReflectionIntersectionType $type)
    {
        $this->isLazy = false;
        $this->isNullable = false;
        $this->isUndefinable = false;
        $this->isData = false;
        $this->isDataCollection = false;
        $this->types = new DataPropertyTypes();

        foreach ($type->getTypes() as $childType) {
            $name = $childType->getName();

            if ($name === 'null') {
                $this->isNullable = true;

                continue;
            }

            if ($name === Undefined::class) {
                $this->isUndefinable = true;

                continue;
            }

            if ($name === Lazy::class) {
                $this->isLazy = true;

                continue;
            }

            if (is_a($name, Data::class, true)) {
                $this->isData = true;
                $this->types->add($name);

                continue;
            }

            if (is_a($name, DataCollection::class, true)) {
                $this->isDataCollection = true;
                $this->types->add($name);

                continue;
            }

            $this->types->add($name);
        }
    }

    private function ensurePropertyIsValid()
    {
        if ($this->isData && $this->types->count() > 1) {
            throw InvalidDataPropertyType::unionWithData($this->property);
        }

        if ($this->isDataCollection && $this->types->count() > 1) {
            throw InvalidDataPropertyType::unionWithDataCollection($this->property);
        }
    }

    private function resolveDataCollectionClass(): string
    {
        if ($attribute = $this->dataCollectionOfAttribute()) {
            return $attribute->class;
        }

        $class = (new DataCollectionAnnotationReader())->getClass($this->property);

        if ($class === null) {
            throw CannotFindDataTypeForProperty::wrongDataCollectionAnnotation($this->className(), $this->name());
        }

        return $class;
    }

    private function loadAttributes(): void
    {
        $validationAttributes = [];

        foreach ($this->property->getAttributes() as $attribute) {
            $initiatedAttribute = $attribute->newInstance();

            if ($initiatedAttribute instanceof ValidationAttribute) {
                $validationAttributes[] = $initiatedAttribute;

                continue;
            }

            if ($initiatedAttribute instanceof WithCast) {
                $this->castAttribute = $initiatedAttribute;

                continue;
            }

            if ($initiatedAttribute instanceof WithTransformer) {
                $this->transformerAttribute = $initiatedAttribute;

                continue;
            }

            if ($initiatedAttribute instanceof DataCollectionOf) {
                $this->dataCollectionOfAttribute = $initiatedAttribute;

                continue;
            }

            if ($initiatedAttribute instanceof WithoutValidation) {
                $this->withValidation = false;

                continue;
            }
        }

        $this->validationAttributes = $validationAttributes;

        if (! isset($this->castAttribute)) {
            $this->castAttribute = null;
        }

        if (! isset($this->transformerAttribute)) {
            $this->transformerAttribute = null;
        }

        if (! isset($this->dataCollectionOfAttribute)) {
            $this->dataCollectionOfAttribute = null;
        }

        if (! isset($this->withValidation)) {
            $this->withValidation = true;
        }
    }
}
