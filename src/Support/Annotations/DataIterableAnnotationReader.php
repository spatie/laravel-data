<?php

namespace Spatie\LaravelData\Support\Annotations;

use Illuminate\Support\Arr;
use phpDocumentor\Reflection\FqsenResolver;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Resolvers\ContextResolver;

/**
 * @note To myself, always use the fully qualified class names in pest tests when using anonymous classes
 */
class DataIterableAnnotationReader
{
    public function __construct(
        protected readonly ContextResolver $contextResolver,
    ) {
    }

    /** @return array<string, DataIterableAnnotation> */
    public function getForClass(ReflectionClass $class): array
    {
        return collect($this->get($class))->keyBy(fn (DataIterableAnnotation $annotation) => $annotation->property)->all();
    }

    public function getForProperty(ReflectionProperty $property): ?DataIterableAnnotation
    {
        /** Also {@see resolveDataClass()}. */
        $annotations = $this->get($property);
        return Arr::first($annotations, fn (DataIterableAnnotation $a) => $a->isData) ?? Arr::first($annotations);
    }

    /** @return DataIterableAnnotation[] */
    public function getAllForProperty(ReflectionProperty $property): array
    {
        return $this->get($property);
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
        $fqsenPattern = '[\\\\\\p{L}0-9_\|]+';
        $typesPattern = '[\\\\\\p{L}0-9_\\|\\[\\]]+';
        $keyPattern = '(?<key>int|string|int\|string|string\|int|array-key)';
        $parameterPattern = '\s*\$?(?<parameter>[\\p{L}0-9_]+)?';

        preg_match_all(
            "/{$kindPattern}(?<types>{$typesPattern}){$parameterPattern}/ui",
            $comment,
            $arrayMatches,
        );

        preg_match_all(
            "/{$kindPattern}(?<collectionClass>{$fqsenPattern})<(?:{$keyPattern}\s*?,\s*?)?(?<dataClass>{$fqsenPattern})>(?:{$typesPattern})*{$parameterPattern}/ui",
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

            $types = $this->resolveTypes($reflection, $dataClass);
            foreach ($types as $resolvedTuple) {
                $annotations[] = new DataIterableAnnotation(
                    type: $resolvedTuple['type'],
                    isData: $resolvedTuple['isData'],
                    keyType: empty($key) ? 'array-key' : $key,
                    property: empty($parameter) ? null : $parameter
                );
            }
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
        $types = $this->resolveTypes($reflection, $class);
        /** Also {@see getForProperty()}. */
        return Arr::first($types, fn (array $type) => $type['isData']) ?? Arr::first($types) ?? [
            'type' => $class,
            'isData' => false,
        ];
    }

    /**
     * @return array{type: string, isData: bool}[]
     */
    protected function resolveTypes(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): array {
        $types = [];
        foreach (explode('|', $class) as $explodedClass) {
            if (in_array($explodedClass, ['int', 'string', 'bool', 'float', 'array', 'object', 'callable', 'iterable', 'mixed'])) {
                $types[] = [
                    'type' => $explodedClass,
                    'isData' => false,
                ];
                continue;
            }

            $explodedClass = ltrim($explodedClass, '\\');
            if (is_subclass_of($explodedClass, BaseData::class)) {
                $types[] = [
                    'type' => $explodedClass,
                    'isData' => true,
                ];
                continue;
            }

            $fcqn = $this->resolveFcqn($reflection, $explodedClass);
            if (is_subclass_of($fcqn, BaseData::class)) {
                $types[] = [
                    'type' => $fcqn,
                    'isData' => true,
                ];
                continue;
            }

            if (class_exists($fcqn)) {
                $types[] = [
                    'type' => $fcqn,
                    'isData' => false,
                ];
                continue;
            }

            $types[] = [
                'type' => $explodedClass,
                'isData' => false,
            ];
        }

        return $types;
    }

    protected function resolveFcqn(
        ReflectionProperty|ReflectionClass|ReflectionMethod $reflection,
        string $class
    ): ?string {
        $context = $this->contextResolver->execute($reflection);

        $type = (new FqsenResolver())->resolve($class, $context);

        return ltrim((string) $type, '\\');
    }
}
