<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Types\NamedType;

class DataReturnType
{
    public function __construct(
        public NamedType $type,
        public DataTypeKind $kind,
    ) {
    }

    public function acceptsType(string $type): bool
    {
        return $this->type->acceptsType($type);
    }
}
