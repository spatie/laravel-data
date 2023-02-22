<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class TransformationContextFactory
{
    use ForwardsToPartialsDefinition;

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param bool $transformValues
     * @param bool $mapPropertyNames
     * @param \Spatie\LaravelData\Support\Wrapping\WrapExecutionType $wrapExecutionType
     */
    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public PartialsDefinition $partialsDefinition = new PartialsDefinition(),
    ) {
    }

    public function get(
        BaseData|BaseDataCollectable $data
    ): TransformationContext {
        return new TransformationContext(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            PartialTransformationContext::create($data, $this->partialsDefinition),
        );
    }

    public function transformValues(bool $transformValues = true): static
    {
        $this->transformValues = $transformValues;

        return $this;
    }

    public function mapPropertyNames(bool $mapPropertyNames = true): static
    {
        $this->mapPropertyNames = $mapPropertyNames;

        return $this;
    }

    public function wrapExecutionType(WrapExecutionType $wrapExecutionType): static
    {
        $this->wrapExecutionType = $wrapExecutionType;

        return $this;
    }

    protected function getPartialsDefinition(): PartialsDefinition
    {
        return $this->partialsDefinition;
    }
}
