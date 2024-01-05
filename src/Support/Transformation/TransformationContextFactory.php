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
     * @param ?SplObjectStorage<Partial> $includedPartials
     * @param ?SplObjectStorage<Partial> $excludedPartials
     * @param ?SplObjectStorage<Partial> $onlyPartials
     * @param ?SplObjectStorage<Partial> $exceptPartials
     */
    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?SplObjectStorage $includedPartials = null,
        public ?SplObjectStorage $excludedPartials = null,
        public ?SplObjectStorage $onlyPartials = null,
        public ?SplObjectStorage $exceptPartials = null,
    ) {
    }

    public function get(
        BaseData|BaseDataCollectable $data,
    ): TransformationContext {
        $includedPartials = null;

        if ($this->includedPartials) {
            $includedPartials = new SplObjectStorage();

            foreach ($this->includedPartials as $include) {
                $resolved = $include->resolve($data);

                if ($resolved) {
                    $includedPartials->attach($resolved);
                }
            }
        }

        $excludedPartials = null;

        if ($this->excludedPartials) {
            $excludedPartials = new SplObjectStorage();

            foreach ($this->excludedPartials as $exclude) {
                $resolved = $exclude->resolve($data);

                if ($resolved) {
                    $excludedPartials->attach($resolved);
                }
            }
        }

        $onlyPartials = null;

        if ($this->onlyPartials) {
            $onlyPartials = new SplObjectStorage();

            foreach ($this->onlyPartials as $only) {
                $resolved = $only->resolve($data);

                if ($resolved) {
                    $onlyPartials->attach($resolved);
                }
            }
        }

        $exceptPartials = null;

        if ($this->exceptPartials) {
            $exceptPartials = new SplObjectStorage();

            foreach ($this->exceptPartials as $except) {
                $resolved = $except->resolve($data);

                if ($resolved) {
                    $exceptPartials->attach($resolved);
                }
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
        if ($this->includedPartials === null) {
            $this->includedPartials = new SplObjectStorage();
        }

        foreach ($partial as $include) {
            $this->includedPartials->attach($include);
        }

        return $this;
    }

    public function addExcludePartial(Partial ...$partial): static
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new SplObjectStorage();
        }

        foreach ($partial as $exclude) {
            $this->excludedPartials->attach($exclude);
        }

        return $this;
    }

    public function addOnlyPartial(Partial ...$partial): static
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new SplObjectStorage();
        }

        foreach ($partial as $only) {
            $this->onlyPartials->attach($only);
        }

        return $this;
    }

    public function addExceptPartial(Partial ...$partial): static
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new SplObjectStorage();
        }

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
        if ($this->includedPartials === null) {
            $this->includedPartials = new SplObjectStorage();
        }

        $this->includedPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeExcludePartials(SplObjectStorage $partials): static
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new SplObjectStorage();
        }

        $this->excludedPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeOnlyPartials(SplObjectStorage $partials): static
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new SplObjectStorage();
        }

        $this->onlyPartials->addAll($partials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     */
    public function mergeExceptPartials(SplObjectStorage $partials): static
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new SplObjectStorage();
        }

        $this->exceptPartials->addAll($partials);

        return $this;
    }

    protected function getPartialsContainer(): object
    {
        return $this;
    }
}
