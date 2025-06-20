<?php

namespace Spatie\LaravelData\Resolvers;

use BackedEnum;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use UnitEnum;

class DataMorphClassResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    /**
     * @param array<array<string, mixed>> $normalizedPayloads
     */
    public function execute(
        DataClass $dataClass,
        array $normalizedPayloads,
    ): ?string {
        if ($dataClass->isAbstract === false || $dataClass->propertyMorphable === false) {
            return null;
        }

        $dataProperties = $dataClass
            ->properties
            ->filter(fn (DataProperty $property) => $property->morphable)
            ->all();

        $morphProperties = [];

        foreach ($dataProperties as $dataProperty) {
            $found = false;

            foreach ($normalizedPayloads as $normalizedPayload) {
                if (array_key_exists($dataProperty->name, $normalizedPayload)) {
                    $morphProperties[$dataProperty->name] = $this->normalizeValue(
                        $dataProperty,
                        $normalizedPayload[$dataProperty->name],
                    );

                    $found = true;

                    break;
                }

                if (array_key_exists($dataProperty->inputMappedName, $normalizedPayload)) {
                    $morphProperties[$dataProperty->name] = $this->normalizeValue(
                        $dataProperty,
                        $normalizedPayload[$dataProperty->inputMappedName],
                    );

                    $found = true;

                    break;
                }
            }

            if ($dataProperty->hasDefaultValue) {
                $morphProperties[$dataProperty->name] = $this->normalizeValue(
                    $dataProperty,
                    $dataProperty->defaultValue,
                );

                continue;
            }

            if ($found === false) {
                return null;
            }
        }

        /** @var class-string<PropertyMorphableData> $baseClass */
        $baseClass = $dataClass->name;

        return $baseClass::morph($morphProperties);
    }

    protected function normalizeValue(
        DataProperty $property,
        mixed $value,
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
