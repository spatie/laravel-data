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
        public PartialTransformationContext $partials = new PartialTransformationContext(),
    ) {
    }

    public function next(
        string $property,
    ): self {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $this->partials->getNested($property)
        );
    }

    public function mergePartials(PartialTransformationContext $partials): self
    {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $this->partials->merge($partials),
        );
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $wrapExecutionType,
            $this->partials,
        );
    }
}
