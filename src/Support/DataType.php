<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Types\Type;

class DataType
{
    /**
     * @param Type $type
     * @param string|null $lazyType
     * @param bool $isOptional
     * @param DataTypeKind $kind
     * @param class-string<BaseData>|null $dataClass
     * @param string|null $dataCollectableClass
     */
    public function __construct(
        public readonly Type $type,
        public readonly ?string $lazyType,
        public readonly bool $isOptional,
        public readonly DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
    ) {
    }

    public function isNullable(): bool
    {
        return $this->type->isNullable;
    }

    public function isMixed(): bool
    {
        return $this->type->isMixed;
    }
}
