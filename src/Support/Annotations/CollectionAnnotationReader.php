<?php

namespace Spatie\LaravelData\Support\Annotations;

use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use ReflectionClass;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\ContextResolver;

class CollectionAnnotationReader
{
    /** @var array<class-string, CollectionAnnotation|null> */
    protected static array $cache = [];

    protected Context $context;

    public function __construct(
        protected readonly ContextResolver $contextResolver,
        protected readonly TypeResolver $typeResolver,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function getForClass(string $className): ?CollectionAnnotation
    {
        if (array_key_exists($className, self::$cache)) {
            return self::$cache[$className];
        }

        $class = new ReflectionClass($className);

        if (empty($class->getDocComment())) {
            return self::$cache[$className] = null;
        }

        if (! $this->isIterable($class)) {
            return self::$cache[$className] = null;
        }

        $type = $this->getCollectionReturnType($class);

        if ($type === null || $type['valueType'] === null) {
            return self::$cache[$className] = null;
        }

        return self::$cache[$className] = new CollectionAnnotation(
            type: $type['valueType'],
            isData: is_subclass_of($type['valueType'], Data::class),
            keyType: $type['keyType'] ?? 'array-key',
        );
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }

    protected function isIterable(ReflectionClass $class): bool
    {
        $collectionInterfaces = [
            Iterator::class,
            IteratorAggregate::class,
        ];

        foreach ($collectionInterfaces as $interface) {
            if ($class->implementsInterface($interface)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{keyType: string|null, valueType: string|null}|null
     */
    protected function getCollectionReturnType(ReflectionClass $class): ?array
    {
        $docBlockFactory = DocBlockFactory::createInstance();

        $this->context = $this->contextResolver->execute($class);

        $docComment = $class->getDocComment();

        if ($docComment === false) {
            return null;
        }

        $docBlock = $docBlockFactory->create($docComment, $this->context);

        $templateTypes = [];
        $keyType = null;
        $valueType = null;

        foreach ($docBlock->getTags() as $tag) {
            if (! $tag instanceof Generic) {
                continue;
            }

            if ($tag->getName() === 'template') {
                $description = $tag->getDescription();

                if (preg_match('/^(\w+)\s+of\s+([^\s]+)/', $description, $matches)) {
                    $templateTypes[$matches[1]] = $this->resolve($matches[2]);
                }

                continue;
            }

            if ($tag->getName() === 'extends') {
                $description = $tag->getDescription();

                if (preg_match('/<\s*([^,\s]+)?\s*(?:,\s*([^>\s]+))?\s*>/', $description, $matches)) {
                    if (count($matches) === 3) {
                        $keyType = $templateTypes[$matches[1]] ?? $this->resolve($matches[1]);
                        $valueType = $templateTypes[$matches[2]] ?? $this->resolve($matches[2]);
                    } else {
                        $keyType = null;
                        $valueType = $templateTypes[$matches[1]] ?? $this->resolve($matches[1]);
                    }

                    $keyType = $keyType ? explode('|', $keyType)[0] : null;
                    $valueType = explode('|', $valueType)[0];

                    return [
                        'keyType' => $keyType,
                        'valueType' => $valueType,
                    ];
                }
            }
        }

        return null;
    }

    protected function resolve(string $type): ?string
    {
        $type = (string) $this->typeResolver->resolve($type, $this->context);

        return $type ? ltrim($type, '\\') : null;
    }
}
