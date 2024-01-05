<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use SplObjectStorage;

class TransformationContextFactory
{
    use ForwardsToPartialsDefinition;

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param SplObjectStorage<Partial> $includedPartials
     * @param SplObjectStorage<Partial> $excludedPartials
     * @param SplObjectStorage<Partial> $onlyPartials
     * @param SplObjectStorage<Partial> $exceptPartials
     */
    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public SplObjectStorage $includedPartials = new SplObjectStorage(),
        public SplObjectStorage $excludedPartials = new SplObjectStorage(),
        public SplObjectStorage $onlyPartials = new SplObjectStorage(),
        public SplObjectStorage $exceptPartials = new SplObjectStorage(),
    ) {
    }

    public function get(
        BaseData|BaseDataCollectable $data,
    ): TransformationContext {
        $includedPartials = new SplObjectStorage();

        foreach ($this->includedPartials as $include) {
            $resolved = $include->resolve($data);

            if($resolved) {
                $includedPartials->attach($resolved);
            }
        }

        $excludedPartials = new SplObjectStorage();

        foreach ($this->excludedPartials as $exclude) {
            $resolved = $exclude->resolve($data);

            if($resolved) {
                $excludedPartials->attach($resolved);
            }
        }

        $onlyPartials = new SplObjectStorage();

        foreach ($this->onlyPartials as $only) {
            $resolved = $only->resolve($data);

            if($resolved) {
                $onlyPartials->attach($resolved);
            }
        }

        $exceptPartials = new SplObjectStorage();

        foreach ($this->exceptPartials as $except) {
            $resolved = $except->resolve($data);

            if($resolved) {
                $exceptPartials->attach($resolved);
            }
        }

        return new TransformationContext(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            $includedPartials,
            $excludedPartials,
            $onlyPartials,
            $exceptPartials,
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

    public function addIncludePartial(Partial ...$partial): static
    {
        foreach ($partial as $include) {
            $this->includedPartials->attach($include);
        }

        return $this;
    }

    public function addExcludePartial(Partial ...$partial): static
    {
        foreach ($partial as $exclude) {
            $this->excludedPartials->attach($exclude);
        }

        return $this;
    }

    public function addOnlyPartial(Partial ...$partial): static
    {
        foreach ($partial as $only) {
            $this->onlyPartials->attach($only);
        }

        return $this;
    }

    public function addExceptPartial(Partial ...$partial): static
    {
        foreach ($partial as $except) {
            $this->exceptPartials->attach($except);
        }

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeIncludePartials(SplObjectStorage $partials): static
    {
        $this->includedPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeExcludePartials(SplObjectStorage $partials): static
    {
        $this->excludedPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeOnlyPartials(SplObjectStorage $partials): static
    {
        $this->onlyPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeExceptPartials(SplObjectStorage $partials): static
    {
        $this->exceptPartials->addAll($partials);

        return $this;
    }

    protected function getPartialsContainer(): object
    {
        return $this;
    }
}
