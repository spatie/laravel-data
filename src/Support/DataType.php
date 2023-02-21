<?php

namespace Spatie\LaravelData\Support;

use Countable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use IteratorAggregate;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataCollectableType;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use TypeError;
use function Pest\Laravel\instance;

/**
 * @property class-string<BaseData>|null $dataClass
 */
class DataType extends Type
{
    public function __construct(
        bool $isNullable,
        bool $isMixed,
        public readonly bool $isLazy,
        public readonly bool $isOptional,
        public readonly DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
        array $acceptedTypes,
    ) {
        parent::__construct($isNullable, $isMixed, $acceptedTypes);
    }
}
