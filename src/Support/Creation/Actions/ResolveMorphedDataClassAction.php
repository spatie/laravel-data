<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use BackedEnum;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use UnitEnum;

class ResolveMorphedDataClassAction
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected ReadDataPropertyAction $readDataPropertyAction,
    ) {
    }

    /**
     * @param array<int, array|Normalized> $normalized
     */
    public function execute(
        CreationContext $creationContext,
        DataClass $dataClass,
        array $normalized
    ): DataClass {
        $morphProperties = [];

        foreach ($dataClass->properties as $property) {
            if (! $property->morphable) {
                continue;
            }

            [$value] = $this->readDataPropertyAction->execute($creationContext, $property, $normalized);

            if ($value instanceof UnknownProperty && ! $property->hasDefaultValue) {
                throw CannotCreateAbstractClass::morphClassWasNotResolved(originalClass: $dataClass->name);
            }

            if ($value instanceof UnknownProperty) {
                $value = $property->defaultValue;
            }

            $morphProperties[$property->name] = $this->morphValue($property, $value);
        }

        /** @var class-string<PropertyMorphableData> $baseClass */
        $baseClass = $dataClass->name;

        $morphedClass = $baseClass::morph($morphProperties);

        if ($morphedClass === null) {
            throw CannotCreateAbstractClass::morphClassWasNotResolved(originalClass: $dataClass->name);
        }

        return $this->dataConfig->getDataClass($morphedClass);
    }

    protected function morphValue(
        DataProperty $property,
        mixed $value
    ): string|int|null|BackedEnum|UnitEnum {
        if ($value === null) {
            return null;
        }

        if (
            (is_string($value) || is_numeric($value))
            && $enumClass = $property->type->findAcceptedTypeForBaseType(BackedEnum::class)
        ) {
            return $enumClass::tryFrom($value);
        }

        return $value;
    }
}
