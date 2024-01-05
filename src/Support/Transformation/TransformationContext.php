<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use SplObjectStorage;
use Stringable;

class TransformationContext implements Stringable
{
    /**
     * @param null|SplObjectStorage<ResolvedPartial> $includedPartials for internal use only
     * @param null|SplObjectStorage<ResolvedPartial> $excludedPartials for internal use only
     * @param null|SplObjectStorage<ResolvedPartial> $onlyPartials     for internal use only
     * @param null|SplObjectStorage<ResolvedPartial> $exceptPartials   for internal use only
     */
    public function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?SplObjectStorage $includedPartials = null,
        public ?SplObjectStorage $excludedPartials = null,
        public ?SplObjectStorage $onlyPartials = null,
        public ?SplObjectStorage $exceptPartials = null,
    ) {
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        // Todo: remove and run directly on object

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

    public function addIncludedResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->includedPartials === null) {
            $this->includedPartials = new SplObjectStorage();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->includedPartials->attach($resolvedPartial);
        }
    }

    public function addExcludedResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new SplObjectStorage();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->excludedPartials->attach($resolvedPartial);
        }
    }

    public function addOnlyResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new SplObjectStorage();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->onlyPartials->attach($resolvedPartial);
        }
    }

    public function addExceptResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new SplObjectStorage();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->exceptPartials->attach($resolvedPartial);
        }
    }

    /**
     * @param SplObjectStorage<ResolvedPartial> $partials
     */
    public function mergeIncludedResolvedPartials(SplObjectStorage $partials): void
    {
        if ($this->includedPartials === null) {
            $this->includedPartials = new SplObjectStorage();
        }

        $this->includedPartials->addAll($partials);
    }

    /**
     * @param SplObjectStorage<ResolvedPartial> $partials
     */
    public function mergeExcludedResolvedPartials(SplObjectStorage $partials): void
    {
        if ($this->excludedPartials === null) {
            $this->excludedPartials = new SplObjectStorage();
        }

        $this->excludedPartials->addAll($partials);
    }

    /**
     * @param SplObjectStorage<ResolvedPartial> $partials
     */
    public function mergeOnlyResolvedPartials(SplObjectStorage $partials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new SplObjectStorage();
        }

        $this->onlyPartials->addAll($partials);
    }

    /**
     * @param SplObjectStorage<ResolvedPartial> $partials
     */
    public function mergeExceptResolvedPartials(SplObjectStorage $partials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new SplObjectStorage();
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
        $output = 'Transformation Context '.spl_object_id($this).PHP_EOL;

        $output .= "- wrapExecutionType: {$this->wrapExecutionType->name}".PHP_EOL;

        if ($this->transformValues) {
            $output .= "- transformValues: true".PHP_EOL;
        }

        if ($this->mapPropertyNames) {
            $output .= "- mapPropertyNames: true".PHP_EOL;
        }

        if ($this->includedPartials !== null && $this->includedPartials->count() > 0) {
            $output .= "- includedPartials:".PHP_EOL;
            foreach ($this->includedPartials as $includedPartial) {
                $output .= "  - {$includedPartial}".PHP_EOL;
            }
        }

        if ($this->excludedPartials !== null && $this->excludedPartials->count() > 0) {
            $output .= "- excludedPartials:".PHP_EOL;
            foreach ($this->excludedPartials as $excludedPartial) {
                $output .= "  - {$excludedPartial}".PHP_EOL;
            }
        }

        if ($this->onlyPartials !== null && $this->onlyPartials->count() > 0) {
            $output .= "- onlyPartials:".PHP_EOL;
            foreach ($this->onlyPartials as $onlyPartial) {
                $output .= "  - {$onlyPartial}".PHP_EOL;
            }
        }

        if ($this->exceptPartials !== null && $this->exceptPartials->count() > 0) {
            $output .= "- exceptPartials:".PHP_EOL;
            foreach ($this->exceptPartials as $exceptPartial) {
                $output .= "  - {$exceptPartial}".PHP_EOL;
            }
        }

        return $output;
    }
}
