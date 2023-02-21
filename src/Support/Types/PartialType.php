<?php

namespace Spatie\LaravelData\Support\Types;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use ReflectionNamedType;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;

class PartialType
{
    public function __construct(
        public readonly string $name,
        public readonly bool $builtIn,
        public readonly array $acceptedTypes,
    ) {
    }

    public static function create(
        ReflectionNamedType $type,
        ?string $class,
    ): self {
        $typeName = $type->getName();

        if ($typeName === 'mixed' || $type->isBuiltin()) {
            return new self($typeName, true, []);
        }

        if ($typeName === 'self' || $typeName === 'static') {
            $typeName = $class;
        }

        $acceptedTypes = array_unique([
            ...array_values(class_parents($typeName)),
            ...array_values(class_implements($typeName)),
        ]);

        return new self(
            name: $typeName,
            builtIn: $type->isBuiltin(),
            acceptedTypes: $acceptedTypes
        );
    }

    public function acceptsType(string $type): bool
    {
        if ($type === $this->name) {
            return true;
        }

        if ($this->builtIn) {
            return false;
        }

        // TODO: move this to some store for caching?
        $baseTypes = class_exists($type)
            ? array_unique([
                ...array_values(class_parents($type)),
                ...array_values(class_implements($type)),
            ])
            : [];

        if (in_array($this->name, [$type, ...$baseTypes], true)) {
            return true;
        }

        return false;
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        if ($class === $this->name) {
            return $class;
        }

        if (in_array($class, $this->acceptedTypes)) {
            return $this->name;
        }

        return null;
    }

    public function isLazy(): bool
    {
        return $this->name === Lazy::class || in_array(Lazy::class, $this->acceptedTypes);
    }

    public function isOptional(): bool
    {
        return $this->name === Optional::class || in_array(Optional::class, $this->acceptedTypes);
    }

    public function getDataTypeKind(): DataTypeKind
    {
        return match (true) {
            in_array(BaseData::class, $this->acceptedTypes) => DataTypeKind::DataObject,
            $this->name === 'array' => DataTypeKind::Array,
            in_array(Enumerable::class, $this->acceptedTypes) => DataTypeKind::Enumerable,
            in_array(DataCollection::class, $this->acceptedTypes) || $this->name === DataCollection::class => DataTypeKind::DataCollection,
            in_array(PaginatedDataCollection::class, $this->acceptedTypes) || $this->name === PaginatedDataCollection::class => DataTypeKind::DataPaginatedCollection,
            in_array(CursorPaginatedDataCollection::class, $this->acceptedTypes) || $this->name === CursorPaginatedDataCollection::class => DataTypeKind::DataCursorPaginatedCollection,
            in_array(Paginator::class, $this->acceptedTypes) || in_array(AbstractPaginator::class, $this->acceptedTypes) => DataTypeKind::Paginator,
            in_array(CursorPaginator::class, $this->acceptedTypes) || in_array(AbstractCursorPaginator::class, $this->acceptedTypes) => DataTypeKind::CursorPaginator,
            default => DataTypeKind::Default,
        };
    }
}
