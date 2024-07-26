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
    public function __construct(
        protected readonly ContextResolver $contextResolver,
        protected readonly TypeResolver $typeResolver,
    ) {
    }

    protected Context $context;

    public function getForClass(ReflectionClass $class): ?CollectionAnnotation
    {
        if (! $this->isCollection($class)) {
            return null;
        }

        $type = $this->getCollectionReturnType($class);

        if ($type === null || $type['valueType'] === null) {
            return null;
        }

        $isData = false;

        if (is_subclass_of($type['valueType'], Data::class)) {
            $isData = true;
        }

        return new CollectionAnnotation(
            type: $type['valueType'],
            isData: $isData,
            keyType: $type['keyType'] ?? 'array-key',
        );
    }

    /**
     * @return array{keyType: string|null, valueType: string|null}|null
     */
    protected function getCollectionReturnType(ReflectionClass $class): ?array
    {
        // Initialize TypeResolver and DocBlockFactory
        $docBlockFactory = DocBlockFactory::createInstance();

        $this->context = $this->contextResolver->get($class);

        // Get the PHPDoc comment of the class
        $docComment = $class->getDocComment();
        if ($docComment === false) {
            return null;
        }

        // Create the DocBlock instance
        $docBlock = $docBlockFactory->create($docComment, $this->context);

        // Initialize variables
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

    protected function isCollection(ReflectionClass $class): bool
    {
        // Check if the class implements common collection interfaces
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

    protected function resolve(string $type): ?string
    {
        $type = (string) $this->typeResolver->resolve($type, $this->context);

        return $type ? ltrim($type, '\\') : null;
    }
}
