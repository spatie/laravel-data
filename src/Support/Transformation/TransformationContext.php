<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use TypeError;

class TransformationContext
{
    public function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public TreeNode $lazyIncluded = new DisabledTreeNode(),
        public TreeNode $lazyExcluded = new DisabledTreeNode(),
        public TreeNode $only = new DisabledTreeNode(),
        public TreeNode $except = new DisabledTreeNode(),
    ) {
    }

    public function next(
        string $property,
    ): self {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $this->lazyIncluded->getNested($property),
            $this->lazyExcluded->getNested($property),
            $this->only->getNested($property),
            $this->except->getNested($property),
        );
    }

    public function merge(LocalTransformationContext $localContext): self
    {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $this->lazyIncluded->merge($localContext->lazyIncluded),
            $this->lazyExcluded->merge($localContext->lazyExcluded),
            $this->only->merge($localContext->only),
            $this->except->merge($localContext->except),
        );
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $wrapExecutionType,
            $this->lazyIncluded,
            $this->lazyExcluded,
            $this->only,
            $this->except,
        );
    }
}
