<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\Transformer;

class TransformationContextFactory
{
    use ForwardsToPartialsDefinition;

    public ?int $maxDepth;

    public bool $throwWhenMaxDepthReached;

    public static function create(): self
    {
        return new self();
    }

    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?GlobalTransformersCollection $transformers = null,
        public ?PartialsCollection $includePartials = null,
        public ?PartialsCollection $excludePartials = null,
        public ?PartialsCollection $onlyPartials = null,
        public ?PartialsCollection $exceptPartials = null,
    ) {
        $this->maxDepth = config('data.max_transformation_depth', null);
        $this->throwWhenMaxDepthReached = config('data.throw_when_max_transformation_depth_reached', true);
    }

    public function get(
        BaseData|BaseDataCollectable $data,
    ): TransformationContext {
        $includePartials = null;

        if ($this->includePartials) {
            $includePartials = new PartialsCollection();

            foreach ($this->includePartials as $include) {
                if ($include->isRequired($data)) {
                    $includePartials->attach($include->reset());
                }
            }
        }

        $excludePartials = null;

        if ($this->excludePartials) {
            $excludePartials = new PartialsCollection();

            foreach ($this->excludePartials as $exclude) {
                if ($exclude->isRequired($data)) {
                    $excludePartials->attach($exclude->reset());
                }
            }
        }

        $onlyPartials = null;

        if ($this->onlyPartials) {
            $onlyPartials = new PartialsCollection();

            foreach ($this->onlyPartials as $only) {
                if ($only->isRequired($data)) {
                    $onlyPartials->attach($only->reset());
                }
            }
        }

        $exceptPartials = null;

        if ($this->exceptPartials) {
            $exceptPartials = new PartialsCollection();

            foreach ($this->exceptPartials as $except) {
                if ($except->isRequired($data)) {
                    $exceptPartials->attach($except->reset());
                }
            }
        }

        return new TransformationContext(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $this->transformers,
            $includePartials,
            $excludePartials,
            $onlyPartials,
            $exceptPartials,
            depth: 0,
            maxDepth: $this->maxDepth,
            throwWhenMaxDepthReached: $this->throwWhenMaxDepthReached,
        );
    }

    public function withValueTransformation(bool $transformValues = true): static
    {
        $this->transformValues = $transformValues;

        return $this;
    }

    public function withoutValueTransformation(bool $withoutValueTransformation = true): static
    {
        $this->transformValues = ! $withoutValueTransformation;

        return $this;
    }

    public function withPropertyNameMapping(bool $mapPropertyNames = true): static
    {
        $this->mapPropertyNames = $mapPropertyNames;

        return $this;
    }

    public function withoutPropertyNameMapping(bool $withoutPropertyNameMapping = true): static
    {
        $this->mapPropertyNames = ! $withoutPropertyNameMapping;

        return $this;
    }

    public function withWrapExecutionType(WrapExecutionType $wrapExecutionType): static
    {
        $this->wrapExecutionType = $wrapExecutionType;

        return $this;
    }

    public function withoutWrapping(): static
    {
        $this->wrapExecutionType = WrapExecutionType::Disabled;

        return $this;
    }

    public function withWrapping(): static
    {
        $this->wrapExecutionType = WrapExecutionType::Enabled;

        return $this;
    }

    public function withTransformer(string $transformable, Transformer|string $transformer): static
    {
        $transformer = is_string($transformer) ? app($transformer) : $transformer;

        if ($this->transformers === null) {
            $this->transformers = new GlobalTransformersCollection();
        }

        $this->transformers->add($transformable, $transformer);

        return $this;
    }

    public function maxDepth(?int $maxDepth, bool $throw = true): static
    {
        $this->maxDepth = $maxDepth;
        $this->throwWhenMaxDepthReached = $throw;

        return $this;
    }

    public function mergeIncludePartials(PartialsCollection $partials): static
    {
        if ($this->includePartials === null) {
            $this->includePartials = new PartialsCollection();
        }

        $this->includePartials->addAll($partials);

        return $this;
    }

    public function mergeExcludePartials(PartialsCollection $partials): static
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new PartialsCollection();
        }

        $this->excludePartials->addAll($partials);

        return $this;
    }

    public function mergeOnlyPartials(PartialsCollection $partials): static
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new PartialsCollection();
        }

        $this->onlyPartials->addAll($partials);

        return $this;
    }

    public function mergeExceptPartials(PartialsCollection $partials): static
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new PartialsCollection();
        }

        $this->exceptPartials->addAll($partials);

        return $this;
    }

    protected function getPartialsContainer(): object
    {
        return $this;
    }
}
