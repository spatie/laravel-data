<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class DataUtilitiesClassPropertyProcessor implements ClassPropertyProcessor
{
    public function execute(
        ReflectionProperty $reflection,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        $dataClass = DataClass::create($reflection->getDeclaringClass());
        $dataProperty = $dataClass->properties->get($reflection->getName());

        if ($dataProperty->hidden) {
            return null;
        }

        if ($dataProperty->outputMappedName) {
            $property->name = new TypeScriptIdentifier($dataProperty->outputMappedName);
        }

        if ($dataProperty->type->kind->isDataCollectable()) {
            $property->type = $this->replaceCollectableTypeWithArray(
                $reflection,
                $property->type,
                $dataProperty
            );
        }

        if (! $property->type instanceof TypeScriptUnion) {
            return $property;
        }

        for ($i = 0; $i < count($property->type->types); $i++) {
            $subType = $property->type->types[$i];

            if ($subType instanceof TypeReference && $this->shouldHideReference($subType)) {
                $property->isOptional = true;

                unset($property->type->types[$i]);
            }
        }

        $property->type->types = array_values($property->type->types);

        return $property;
    }

    protected function replaceCollectableTypeWithArray(
        ReflectionProperty $reflection,
        TypeScriptNode $node,
        DataProperty $dataProperty
    ): TypeScriptNode {
        if ($node instanceof TypeScriptUnion) {
            foreach ($node->types as $i => $subNode) {
                $node->types[$i] = $this->replaceCollectableTypeWithArray($reflection, $subNode, $dataProperty);
            }

            return $node;
        }

        if (
            $node instanceof TypeScriptGeneric
            && $node->type instanceof TypeReference
            && $this->findReplacementForDataCollectable($node->type)
        ) {
            $node->type = $this->findReplacementForDataCollectable($node->type);

            return $node;
        }

        if (
            $node instanceof TypeReference
            && $this->findReplacementForDataCollectable($node)
            && $dataProperty->type->dataClass
        ) {
            return new TypeScriptGeneric(
                $this->findReplacementForDataCollectable($node),
                [new TypeReference(new ClassStringReference($dataProperty->type->dataClass))]
            );
        }

        return $node;
    }

    protected function findReplacementForDataCollectable(
        TypeReference $reference
    ): ?TypeScriptNode {
        if (! $reference->reference instanceof ClassStringReference) {
            return null;
        }

        if ($reference->reference->classString === DataCollection::class) {
            return new TypeScriptIdentifier('Array');
        }

        if ($reference->reference->classString === PaginatedDataCollection::class) {
            return new TypeReference(new ClassStringReference(LengthAwarePaginator::class));
        }

        if ($reference->reference->classString === CursorPaginatedDataCollection::class) {
            return new TypeReference(new ClassStringReference(CursorPaginator::class));
        }

        return null;
    }

    protected function shouldHideReference(
        TypeReference $reference
    ): bool {
        if (! $reference->reference instanceof ClassStringReference) {
            return false;
        }

        return is_a($reference->reference->classString, Lazy::class, true)
            || is_a($reference->reference->classString, Optional::class, true);
    }
}
