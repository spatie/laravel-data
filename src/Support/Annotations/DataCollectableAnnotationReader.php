<?php

namespace Spatie\LaravelData\Support\Annotations;

use Illuminate\Support\Arr;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;

/**
 * @note To myself, always use the fully qualified class names in pest tests when using anonymous classes
 */
class DataCollectableAnnotationReader
{
    /** @var array<string, Context> */
    protected static array $contexts = [];

    /** @return array<string, DataCollectableAnnotation> */
    public function getForClass(ReflectionClass $class): array
    {
        return collect($this->get($class))->keyBy(fn (DataCollectableAnnotation $annotation) => $annotation->property)->all();
    }

    public function getForProperty(ReflectionProperty $property): ?DataCollectableAnnotation
    {
        return Arr::first($this->get($property));
    }

    /** @return array<string, DataCollectableAnnotation> */
    public function getForMethod(ReflectionMethod $method): array
    {
        return collect($this->get($method))->keyBy(fn (DataCollectableAnnotation $annotation) => $annotation->property)->all();
    }

    /** @return DataCollectableAnnotation[] */
    protected function get(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection
    ): array {
        $comment = $reflection->getDocComment();

        if ($comment === false) {
            return [];
        }

        $comment = str_replace('?', '', $comment);

        $kindPattern = '(?:@property|@var|@param)\s*';
        $fqsenPattern = '[\\\\a-z0-9_\|]+';
        $typesPattern = '[\\\\a-z0-9_\\|\\[\\]]+';
        $keyPattern = '(?:int|string|\(int\|string\)|array-key)';
        $parameterPattern = '\s*\$?(?<parameter>[a-z0-9_]+)?';

        preg_match_all(
            "/{$kindPattern}(?<types>{$typesPattern}){$parameterPattern}/i",
            $comment,
            $arrayMatches,
        );

        preg_match_all(
            "/{$kindPattern}(?<collectionClass>{$fqsenPattern})<(?:{$keyPattern},\s*)?(?<dataClass>{$fqsenPattern})>{$parameterPattern}/i",
            $comment,
            $collectionMatches,
        );

        return [
            ...$this->resolveArrayAnnotations($reflection, $arrayMatches),
            ...$this->resolveCollectionAnnotations($reflection, $collectionMatches),
        ];
    }

    protected function resolveArrayAnnotations(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        array $arrayMatches
    ): array {
        $annotations = [];

        foreach ($arrayMatches['types'] as $index => $types) {
            $parameter = $arrayMatches['parameter'][$index];

            $arrayType = Arr::first(
                explode('|', $types),
                fn (string $type) => str_contains($type, '[]'),
            );

            if(empty($arrayType)) {
                continue;
            }

            $dataClass = $this->resolveDataClass(
                $reflection,
                str_replace('[]', '', $arrayType)
            );

            if ($dataClass === null) {
                continue;
            }

            $annotations[] = new DataCollectableAnnotation(
                $dataClass,
                null,
                empty($parameter) ? null : $parameter
            );
        }

        return $annotations;
    }

    protected function resolveCollectionAnnotations(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        array $collectionMatches
    ): array {
        $annotations = [];

        foreach ($collectionMatches['dataClass'] as $index => $dataClass) {
            $parameter = $collectionMatches['parameter'][$index];

            $dataClass = $this->resolveDataClass($reflection, $dataClass);

            if ($dataClass === null) {
                continue;
            }

            $annotations[] = new DataCollectableAnnotation(
                $dataClass,
                null,
                empty($parameter) ? null : $parameter
            );
        }

        return $annotations;
    }

    protected function resolveDataClass(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): ?string {
        if (str_contains($class, '|')) {
            foreach (explode('|', $class) as $explodedClass) {
                if ($foundClass = $this->resolveDataClass($reflection, $explodedClass)) {
                    return $foundClass;
                }
            }

            return null;
        }

        $class = ltrim($class, '\\');

        if (is_subclass_of($class, BaseData::class)) {
            return $class;
        }

        $class = $this->resolveFcqn($reflection, $class);

        if (is_subclass_of($class, BaseData::class)) {
            return $class;
        }

        return null;
    }

    protected function resolveFcqn(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): ?string {
        $context = $this->getContext($reflection);

        $type = (new FqsenResolver())->resolve($class, $context);

        return ltrim((string) $type, '\\');
    }

    protected function getContext(ReflectionProperty|ReflectionClass|ReflectionMethod $reflection): Context
    {
        $reflectionClass = $reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod
            ? $reflection->getDeclaringClass()
            : $reflection;

        return static::$contexts[$reflectionClass->getName()] ??= (new ContextFactory())->createFromReflector($reflectionClass);
    }
}
