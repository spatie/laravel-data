<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Partials\ResolvedPartialsCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class TransformationContextFactory
{
    use ForwardsToPartialsDefinition;

    public static function create(): self
    {
        return new self();
    }

    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?PartialsCollection $includedPartials = null,
        public ?PartialsCollection $excludedPartials = null,
        public ?PartialsCollection $onlyPartials = null,
        public ?PartialsCollection $exceptPartials = null,
    ) {
    }

    public function get(
        BaseData|BaseDataCollectable $data,
    ): TransformationContext {
        $includedPartials = null;

        if ($this->includedPartials) {
            $includedPartials = new ResolvedPartialsCollection();

            foreach ($this->includedPartials as $include) {
                $resolved = $include->resolve($data);

                if ($resolved) {
                    $includedPartials->attach($resolved);
                }
            }
        }

        $excludedPartials = null;

        if ($this->excludedPartials) {
            $excludedPartials = new ResolvedPartialsCollection();

            foreach ($this->excludedPartials as $exclude) {
                $resolved = $exclude->resolve($data);

                if ($resolved) {
                    $excludedPartials->attach($resolved);
                }
            }
        }

        $onlyPartials = null;

        if ($this->onlyPartials) {
            $onlyPartials = new ResolvedPartialsCollection();

            foreach ($this->onlyPartials as $only) {
                $resolved = $only->resolve($data);

                if ($resolved) {
                    $onlyPartials->attach($resolved);
                }
            }
        }

        $exceptPartials = null;

        if ($this->exceptPartials) {
            $exceptPartials = new ResolvedPartialsCollection();

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
            $this->includedPartials = new PartialsCollection();
        }

        foreach ($partial as $include) {
            $this->includedPartials->attach($include);
        }

        return $this;
    }

    public function addExcludePartial(Partial ...$partial): static
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new PartialsCollection();
        }

        foreach ($partial as $exclude) {
            $this->excludedPartials->attach($exclude);
        }

        return $this;
    }

    public function addOnlyPartial(Partial ...$partial): static
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new PartialsCollection();
        }

        foreach ($partial as $only) {
            $this->onlyPartials->attach($only);
        }

        return $this;
    }

    public function addExceptPartial(Partial ...$partial): static
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new PartialsCollection();
        }

        foreach ($partial as $except) {
            $this->exceptPartials->attach($except);
        }

        return $this;
    }

    public function mergeIncludePartials(PartialsCollection $partials): static
    {
        if ($this->includedPartials === null) {
            $this->includedPartials = new PartialsCollection();
        }

        $this->includedPartials->addAll($partials);

        return $this;
    }

    public function mergeExcludePartials(PartialsCollection $partials): static
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new PartialsCollection();
        }

        $this->excludedPartials->addAll($partials);

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
