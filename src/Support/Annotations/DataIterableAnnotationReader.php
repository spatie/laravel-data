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
class DataIterableAnnotationReader
{
    /** @var array<string, Context> */
    protected static array $contexts = [];

    /** @return array<string, DataIterableAnnotation> */
    public function getForClass(ReflectionClass $class): array
    {
        return collect($this->get($class))->keyBy(fn (DataIterableAnnotation $annotation) => $annotation->property)->all();
    }

    public function getForProperty(ReflectionProperty $property): ?DataIterableAnnotation
    {
        return Arr::first($this->get($property));
    }

    /** @return array<string, DataIterableAnnotation> */
    public function getForMethod(ReflectionMethod $method): array
    {
        return collect($this->get($method))->keyBy(fn (DataIterableAnnotation $annotation) => $annotation->property)->all();
    }

    /** @return DataIterableAnnotation[] */
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
        $keyPattern = '(?<key>int|string|int\|string|string\|int|array-key)';
        $parameterPattern = '\s*\$?(?<parameter>[a-z0-9_]+)?';

        preg_match_all(
            "/{$kindPattern}(?<types>{$typesPattern}){$parameterPattern}/i",
            $comment,
            $arrayMatches,
        );

        preg_match_all(
            "/{$kindPattern}(?<collectionClass>{$fqsenPattern})<(?:{$keyPattern}\s*?,\s*?)?(?<dataClass>{$fqsenPattern})>{$parameterPattern}/i",
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

            if (empty($arrayType)) {
                continue;
            }

            $resolvedTuple = $this->resolveDataClass(
                $reflection,
                str_replace('[]', '', $arrayType)
            );

            $annotations[] = new DataIterableAnnotation(
                type: $resolvedTuple['type'],
                isData: $resolvedTuple['isData'],
                property: empty($parameter) ? null : $parameter
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
            $key = $collectionMatches['key'][$index];

            $resolvedTuple = $this->resolveDataClass($reflection, $dataClass);

            $annotations[] = new DataIterableAnnotation(
                type: $resolvedTuple['type'],
                isData: $resolvedTuple['isData'],
                keyType: empty($key) ? 'array-key' : $key,
                property: empty($parameter) ? null : $parameter
            );
        }

        return $annotations;
    }

    /**
     * @return array{type: string, isData: bool}
     */
    protected function resolveDataClass(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): array {
        if (str_contains($class, '|')) {
            $possibleNonDataType = null;

            foreach (explode('|', $class) as $explodedClass) {
                $resolvedTuple = $this->resolveDataClass($reflection, $explodedClass);

                if ($resolvedTuple['isData']) {
                    return $resolvedTuple;
                }

                $possibleNonDataType = $resolvedTuple['type'];
            }

            return [
                'type' => $possibleNonDataType,
                'isData' => false,
            ];
        }

        if (in_array($class, ['int', 'string', 'bool', 'float', 'array', 'object', 'callable', 'iterable', 'mixed'])) {
            return [
                'type' => $class,
                'isData' => false,
            ];
        }

        $class = ltrim($class, '\\');

        if (is_subclass_of($class, BaseData::class)) {
            return [
                'type' => $class,
                'isData' => true,
            ];
        }

        $fcqn = $this->resolveFcqn($reflection, $class);

        if (is_subclass_of($fcqn, BaseData::class)) {
            return [
                'type' => $fcqn,
                'isData' => true,
            ];
        }

        if (class_exists($fcqn)) {
            return [
                'type' => $fcqn,
                'isData' => false,
            ];
        }

        return [
            'type' => $class,
            'isData' => false,
        ];
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
