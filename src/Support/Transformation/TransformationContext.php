<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Partials\ResolvedPartialsCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Stringable;

class TransformationContext implements Stringable
{
    /**
     * @note Do not add extra partials here
     */
    public function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?ResolvedPartialsCollection $includedPartials = null,
        public ?ResolvedPartialsCollection $excludedPartials = null,
        public ?ResolvedPartialsCollection $onlyPartials = null,
        public ?ResolvedPartialsCollection $exceptPartials = null,
    ) {
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        $this->wrapExecutionType = $wrapExecutionType;

        return $this;
    }

    public function addIncludedResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->includedPartials === null) {
            $this->includedPartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->includedPartials->attach($resolvedPartial);
        }
    }

    public function addExcludedResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->excludedPartials->attach($resolvedPartial);
        }
    }

    public function addOnlyResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->onlyPartials->attach($resolvedPartial);
        }
    }

    public function addExceptResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->exceptPartials->attach($resolvedPartial);
        }
    }

    public function mergeIncludedResolvedPartials(ResolvedPartialsCollection $partials): void
    {
        if ($this->includedPartials === null) {
            $this->includedPartials = new ResolvedPartialsCollection();
        }

        $this->includedPartials->addAll($partials);
    }

    public function mergeExcludedResolvedPartials(ResolvedPartialsCollection $partials): void
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new ResolvedPartialsCollection();
        }

        $this->excludedPartials->addAll($partials);
    }

    public function mergeOnlyResolvedPartials(ResolvedPartialsCollection $partials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new ResolvedPartialsCollection();
        }

        $this->onlyPartials->addAll($partials);
    }

    public function mergeExceptResolvedPartials(ResolvedPartialsCollection $partials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new ResolvedPartialsCollection();
        }

        $this->exceptPartials->addAll($partials);
    }

    public function rollBackPartialsWhenRequired(): void
    {
        if ($this->includedPartials !== null) {
            foreach ($this->includedPartials as $includedPartial) {
                $includedPartial->rollbackWhenRequired();
            }
        }

        if ($this->excludedPartials !== null) {
            foreach ($this->excludedPartials as $excludedPartial) {
                $excludedPartial->rollbackWhenRequired();
            }
        }

        if ($this->onlyPartials !== null) {
            foreach ($this->onlyPartials as $onlyPartial) {
                $onlyPartial->rollbackWhenRequired();
            }
        }

        if ($this->exceptPartials !== null) {
            foreach ($this->exceptPartials as $exceptPartial) {
                $exceptPartial->rollbackWhenRequired();
            }
        }
    }

    public function __clone(): void
    {
        if ($this->includedPartials !== null) {
            $this->includedPartials = clone $this->includedPartials;
        }

        if ($this->excludedPartials !== null) {
            $this->excludedPartials = clone $this->excludedPartials;
        }

        if ($this->onlyPartials !== null) {
            $this->onlyPartials = clone $this->onlyPartials;
        }

        if ($this->exceptPartials !== null) {
            $this->exceptPartials = clone $this->exceptPartials;
        }
    }

    public function __toString(): string
    {
        $output = 'Transformation Context ('.spl_object_id($this).')'.PHP_EOL;

        $output .= "- wrapExecutionType: {$this->wrapExecutionType->name}".PHP_EOL;

        if ($this->transformValues) {
            $output .= "- transformValues: true".PHP_EOL;
        }

        if ($this->mapPropertyNames) {
            $output .= "- mapPropertyNames: true".PHP_EOL;
        }

        if ($this->includedPartials !== null && $this->includedPartials->count() > 0) {
            $output .= $this->includedPartials;
        }

        if ($this->excludedPartials !== null && $this->excludedPartials->count() > 0) {
            $output .= $this->excludedPartials;
        }

        if ($this->onlyPartials !== null && $this->onlyPartials->count() > 0) {
            $output .= $this->onlyPartials;
        }

        if ($this->exceptPartials !== null && $this->exceptPartials->count() > 0) {
            $output .= $this->exceptPartials;
        }

        return $output;
    }
}
