<?php

namespace Spatie\LaravelData\Support;

use Exception;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataValidationAttribute;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Str;

class DataProperty
{
    private bool $isLazy;

    private bool $isNullable;

    private bool $isBuiltIn;

    private bool $isData;

    private bool $isDataCollection;

    private string $dataClass;

    private array $types;

    /** @var \Spatie\LaravelData\Attributes\DataValidationAttribute[] */
    private array $validationAttributes;

    private ?WithCast $castAttribute;

    public static function create(ReflectionProperty $property): static
    {
        return new self($property);
    }

    public function __construct(
        protected ReflectionProperty $property
    ) {
        $type = $this->property->getType();

        match (true) {
            $type === null => $this->processNoType(),
            $type instanceof ReflectionNamedType => $this->processNamedType($type),
            $type instanceof ReflectionUnionType => $this->processUnionType($type),
            default => throw new Exception("Unknown reflection type")
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

    public function isBuiltIn(): bool
    {
        return $this->isBuiltIn;
    }

    public function isData(): bool
    {
        return $this->isData;
    }

    public function isDataCollection(): bool
    {
        return $this->isDataCollection;
    }

    public function types(): array
    {
        return $this->types;
    }

    public function name(): string
    {
        return $this->property->getName();
    }

    public function validationAttributes(): array
    {
        if (! isset($this->validationAttributes)) {
            $this->loadAttributes();
        }

        return $this->validationAttributes;
    }

    public function castAttribute(): ?WithCast
    {
        if (! isset($this->castAttribute)) {
            $this->loadAttributes();
        }

        return $this->castAttribute;
    }

    /**
     * @return class-string<\Spatie\LaravelData\Data>
     */
    public function getDataClass(): string
    {
        if (isset($this->dataClass)) {
            return $this->dataClass;
        }

        if ($this->isData) {
            return $this->dataClass = current($this->types);
        }

        if ($this->isDataCollection) {
            $comment = $this->property->getDocComment();

            if ($comment === false) {
                throw new Exception('Could not resolve data class for property');
            }

            // TODO: make this more robust, because it isnt
            return $this->dataClass = Str::of($comment)->after('@var \\')->before('[]');
        }

        throw new Exception('Property type is not a data object or data collection object');
    }

    public function reflection(): ReflectionProperty
    {
        return $this->property;
    }

    private function processNoType(): void
    {
        $this->isLazy = false;
        $this->isNullable = true;
        $this->isBuiltIn = true;
        $this->isData = false;
        $this->isDataCollection = false;
        $this->types = [];
    }

    private function processNamedType(ReflectionNamedType $type)
    {
        $name = $type->getName();

        if (is_a($name, Lazy::class, true)) {
            throw InvalidDataPropertyType::onlyLazy($this->property);
        }

        $this->isLazy = false;
        $this->isBuiltIn = $this->isTypeBuiltIn($name);
        $this->isData = is_a($name, Data::class, true);
        $this->isDataCollection = is_a($name, DataCollection::class, true);
        $this->isNullable = $type->allowsNull();
        $this->types = [$name];
    }

    private function processUnionType(ReflectionUnionType $type)
    {
        $types = $type->getTypes();

        $this->isLazy = false;
        $this->isNullable = false;
        $this->isData = false;
        $this->isDataCollection = false;
        $this->types = [];

        foreach ($types as $childType) {
            $name = $childType->getName();

            if ($name === 'null') {
                $this->isNullable = true;

                continue;
            }

            if ($this->isTypeBuiltIn($name)) {
                $this->isBuiltIn = true;
                $this->types[] = $name;

                continue;
            }

            if ($name === Lazy::class) {
                $this->isLazy = true;

                continue;
            }

            if (is_a($name, Data::class, true)) {
                $this->isData = true;
                $this->types[] = $name;

                continue;
            }

            if (is_a($name, DataCollection::class, true)) {
                $this->isDataCollection = true;
                $this->types[] = $name;

                continue;
            }

            $this->types[] = $name;
        }
    }

    private function isTypeBuiltIn(string $name): bool
    {
        return in_array($name, ['int', 'string', 'bool', 'array', 'float']);
    }

    private function ensurePropertyIsValid()
    {
        if ($this->isData && count($this->types) > 1) {
            throw InvalidDataPropertyType::unionWithData($this->property);
        }

        if ($this->isDataCollection && count($this->types) > 1) {
            throw InvalidDataPropertyType::unionWithDataCollection($this->property);
        }
    }

    private function loadAttributes(): void
    {
        $validationAttributes = [];

        foreach ($this->property->getAttributes() as $attribute) {
            $initiatedAttribute = $attribute->newInstance();

            if ($initiatedAttribute instanceof DataValidationAttribute) {
                $validationAttributes[] = $initiatedAttribute;

                continue;
            }

            if ($initiatedAttribute instanceof WithCast) {
                $this->castAttribute = $initiatedAttribute;

                continue;
            }
        }

        $this->validationAttributes = $validationAttributes;

        if (! isset($this->castAttribute)) {
            $this->castAttribute = null;
        }
    }
}
