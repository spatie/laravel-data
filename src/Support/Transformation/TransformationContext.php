<?php

namespace Spatie\LaravelData\Support\Transformation;

use Exception;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use SplObjectStorage;

class TransformationContext
{
    /**
     * @param SplObjectStorage<ResolvedPartial> $includedPartials
     * @param SplObjectStorage<ResolvedPartial> $excludedPartials
     * @param SplObjectStorage<ResolvedPartial> $onlyPartials
     * @param SplObjectStorage<ResolvedPartial> $exceptPartials
     */
    public function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public SplObjectStorage $includedPartials = new SplObjectStorage(),
        public SplObjectStorage $excludedPartials = new SplObjectStorage(),
        public SplObjectStorage $onlyPartials = new SplObjectStorage(),
        public SplObjectStorage $exceptPartials = new SplObjectStorage(),
    ) {
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        return new self(
            $this->transformValues,
            $this->mapPropertyNames,
            $wrapExecutionType,
            $this->includedPartials,
            $this->excludedPartials,
            $this->onlyPartials,
            $this->exceptPartials,
        );
    }
}
