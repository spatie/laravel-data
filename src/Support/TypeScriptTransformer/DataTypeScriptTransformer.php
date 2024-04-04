<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\Types\StructType;

class DataTypeScriptTransformer extends DtoTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(BaseData::class);
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new RemoveLazyTypeProcessor(),
            new RemoveOptionalTypeProcessor(),
            new DtoCollectionTypeProcessor(),
        ];
    }


    protected function transformProperties(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        $dataClass = app(DataConfig::class)->getDataClass($class->getName());

        $isOptional = $dataClass->attributes->contains(
            fn (object $attribute) => $attribute instanceof TypeScriptOptional
        );

        return array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($isOptional, $dataClass, $missingSymbols) {
                /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
                $dataProperty = $dataClass->properties[$property->getName()];

                $type = $this->resolveTypeForProperty($property, $dataProperty, $missingSymbols);

                if ($type === null) {
                    return $carry;
                }

                $isOptional = $isOptional
                    || $dataProperty->attributes->contains(
                        fn (object $attribute) => $attribute instanceof TypeScriptOptional
                    )
                    || ($dataProperty->type->lazyType && $dataProperty->type->lazyType !== ClosureLazy::class)
                    || $dataProperty->type->isOptional;

                $transformed = $this->typeToTypeScript(
                    $type,
                    $missingSymbols,
                    $property->getDeclaringClass()->getName(),
                );

                $propertyName = $dataProperty->outputMappedName ?? $dataProperty->name;

                if (! preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $propertyName)) {
                    $propertyName = "'{$propertyName}'";
                }

                return $isOptional
                    ? "{$carry}{$propertyName}?: {$transformed};".PHP_EOL
                    : "{$carry}{$propertyName}: {$transformed};".PHP_EOL;
            },
            ''
        );
    }

    protected function resolveTypeForProperty(
        ReflectionProperty $property,
        DataProperty $dataProperty,
        MissingSymbolsCollection $missingSymbols,
    ): ?Type {
        if (! $dataProperty->type->kind->isDataCollectable()) {
            return $this->reflectionToType(
                $property,
                $missingSymbols,
                ...$this->typeProcessors()
            );
        }

        $collectionType = match ($dataProperty->type->kind) {
            DataTypeKind::DataCollection, DataTypeKind::DataArray, DataTypeKind::DataEnumerable, DataTypeKind::Array, DataTypeKind::Enumerable => $this->dataCollectionType(
                $dataProperty->type->dataClass,
                $dataProperty->type->iterableKeyType
            ),
            DataTypeKind::DataPaginator, DataTypeKind::DataPaginatedCollection, DataTypeKind::Paginator => $this->paginatedCollectionType($dataProperty->type->dataClass),
            DataTypeKind::DataCursorPaginator, DataTypeKind::DataCursorPaginatedCollection, DataTypeKind::CursorPaginator => $this->cursorPaginatedCollectionType($dataProperty->type->dataClass),
            default => throw new RuntimeException('Cannot end up here since the type is dataCollectable')
        };

        if ($dataProperty->type->isNullable) {
            return new Nullable($collectionType);
        }

        return $collectionType;
    }

    protected function dataCollectionType(string $class, ?string $keyType): Type
    {
        $keyType = match ($keyType) {
            'string' => new String_(),
            'int' => null,
            default => new Compound([new String_(), new Integer()]),
        };

        return new Array_(
            new Object_(new Fqsen("\\{$class}")),
            $keyType
        );
    }

    protected function defaultCollectionType(string $class): Type
    {
        return new Array_(new Object_(new Fqsen("\\{$class}")));
    }

    protected function paginatedCollectionType(string $class): Type
    {
        return new StructType([
            'data' => $this->defaultCollectionType($class),
            'links' => new Array_(new StructType([
                'url' => new Nullable(new String_()),
                'label' => new String_(),
                'active' => new Boolean(),
            ])),
            'meta' => new StructType([
                'current_page' => new Integer(),
                'first_page_url' => new String_(),
                'from' => new Nullable(new Integer()),
                'last_page' => new Integer(),
                'last_page_url' => new String_(),
                'next_page_url' => new Nullable(new String_()),
                'path' => new String_(),
                'per_page' => new Integer(),
                'prev_page_url' => new Nullable(new String_()),
                'to' => new Nullable(new Integer()),
                'total' => new Integer(),

            ]),
        ]);
    }

    protected function cursorPaginatedCollectionType(string $class): Type
    {
        return new StructType([
            'data' => $this->defaultCollectionType($class),
            'links' => new Array_(),
            'meta' => new StructType([
                'path' => new String_(),
                'per_page' => new Integer(),
                'next_cursor' => new Nullable(new String_()),
                'next_cursor_url' => new Nullable(new String_()),
                'prev_cursor' => new Nullable(new String_()),
                'prev_cursor_url' => new Nullable(new String_()),
            ]),
        ]);
    }
}
