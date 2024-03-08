<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\Types\Type;

class DataPropertyType extends DataType
{
    /**
     * @param class-string<Lazy>|null $lazyType
     */
    public function __construct(
        Type $type,
        public readonly bool $isOptional,
        bool $isNullable,
        bool $isMixed,
        public readonly ?string $lazyType,
        // @note for now we have a one data type per type rule
        // Meaning a type can be a data object of some type, data collection of some type or something else
        // If we want to support multiple types in the future all we need to do is replace calls to these
        // properties and handle everything correctly
        DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
        public readonly ?string $iterableClass,
        public readonly ?string $iterableItemType,
        public readonly ?string $iterableKeyType,
    ) {
        parent::__construct($type, $isNullable, $isMixed, $kind);
    }
}
