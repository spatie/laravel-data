<?php

namespace Spatie\LaravelData\Support\Annotations\PhpDocumentorTypes;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;

class ArrayWithoutMixedDefault extends Array_
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
}
