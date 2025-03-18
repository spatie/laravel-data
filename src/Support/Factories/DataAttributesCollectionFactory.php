<?php

namespace Spatie\LaravelData\Support\Factories;

use Attribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Resource;
use Spatie\LaravelData\Support\DataAttributesCollection;
use Spatie\LaravelData\Support\DataClass;

class DataAttributesCollectionFactory
{
    public static function buildFromReflectionClass(ReflectionClass $reflectionClass): DataAttributesCollection
    {
        $attributeGroups = [
            $reflectionClass->getAttributes(),
        ];

        while ($parent = static::findParentReflectionClass($reflectionClass)) {
            $attributeGroups[] = $parent->getAttributes();

            $reflectionClass = $parent;
        }

        return new DataAttributesCollection(
            static::mapAttributesIntoGroups(array_merge(...$attributeGroups))
        );
    }

    public static function buildFromReflectionProperty(ReflectionProperty $reflectionProperty): DataAttributesCollection
    {
        return new DataAttributesCollection(
            static::mapAttributesIntoGroups($reflectionProperty->getAttributes())
        );
    }

    protected static function findParentReflectionClass(ReflectionClass $reflectionClass): ?ReflectionClass
    {
        $parent = $reflectionClass->getParentClass();

        if ($parent === false) {
            return null;
        }

        if ($parent->name === DataClass::class || $parent->name === Dto::class || $parent->name === Resource::class) {
            return null;
        }

        return $parent;
    }

    /**
     * @param array<int, ReflectionAttribute> $reflectionAttributes
     *
     * @return array<string, array<int, object&Attribute>>
     */
    protected static function mapAttributesIntoGroups(array $reflectionAttributes): array
    {
        $attributes = [];

        foreach ($reflectionAttributes as $reflectionAttribute) {
            if (! class_exists($reflectionAttribute->getName())) {
                continue;
            }

            $attribute = $reflectionAttribute->newInstance();

            $attributes[$reflectionAttribute->getName()][] = $attribute;

            foreach (class_implements($reflectionAttribute->getName()) as $interface) {
                $attributes[$interface][] = $attribute;
            }

            foreach (class_parents($reflectionAttribute->getName()) as $parent) {
                $attributes[$parent][] = $attribute;
            }
        }

        return $attributes;
    }
}
