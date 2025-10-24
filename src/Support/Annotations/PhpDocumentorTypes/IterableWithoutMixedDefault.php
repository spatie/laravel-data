<?php

namespace Spatie\LaravelData\Support\Annotations\PhpDocumentorTypes;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Mixed_;

/**
 * Can't extend {@see \phpDocumentor\Reflection\Types\Iterable_} because it's final.
 */
class IterableWithoutMixedDefault extends AbstractList
{
    /** @var Type|null */
    protected $originalValueType;

    /**
     * Initializes this representation of an array with the given Type.
     */
    public function __construct(?Type $valueType = null, ?Type $keyType = null)
    {
        parent::__construct($valueType, $keyType);
        $this->originalValueType = $valueType;
    }

    public function getOriginalValueType(): ?Type
    {
        return $this->originalValueType;
    }

    public function __toString(): string
    {
        if ($this->keyType) {
            return 'iterable<' . $this->keyType . ',' . $this->valueType . '>';
        }

        if ($this->valueType instanceof Mixed_) {
            return 'iterable';
        }

        return 'iterable<' . $this->valueType . '>';
    }
}
