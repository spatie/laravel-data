<?php

namespace Spatie\LaravelData\Support\Types;

use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;

class NamedType extends Type
{
    public readonly bool $isCastable;

    /**
     * @param string $name
     * @param bool $builtIn
     * @param array<class-string> $acceptedTypes
     * @param DataTypeKind $kind
     * @param class-string<BaseData>|null $dataClass
     * @param string|class-string|null $dataCollectableClass
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $builtIn,
        public readonly array $acceptedTypes,
        public readonly DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
    ) {
        $this->isCastable = in_array(Castable::class, $this->acceptedTypes);
    }

    public function acceptsType(string $type): bool
    {
        if ($type === $this->name) {
            return true;
        }

        if ($this->builtIn) {
            return false;
        }

        if (in_array($this->name, [$type, ...AcceptedTypesStorage::getAcceptedTypes($type)], true)) {
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

    public function getAcceptedTypes(): array
    {
        return [
            $this->name => $this->acceptedTypes,
        ];
    }

    public function isCreationContext(): bool
    {
        return $this->name === CreationContext::class;
    }
}
