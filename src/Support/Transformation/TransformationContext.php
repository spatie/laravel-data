<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Partials\ResolvedPartialsCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Stringable;

class TransformationContext implements Stringable
{
    /**
     * @note Do not add extra partials here manually
     */
    public function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public ?GlobalTransformersCollection $transformers = null,
        public ?ResolvedPartialsCollection $includePartials = null,
        public ?ResolvedPartialsCollection $excludePartials = null,
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
        if ($this->includePartials === null) {
            $this->includePartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->includePartials->attach($resolvedPartial);
        }
    }

    public function addExcludedResolvedPartial(ResolvedPartial ...$resolvedPartials): void
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new ResolvedPartialsCollection();
        }

        foreach ($resolvedPartials as $resolvedPartial) {
            $this->excludePartials->attach($resolvedPartial);
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
        if ($this->includePartials === null) {
            $this->includePartials = new ResolvedPartialsCollection();
        }

        $this->includePartials->addAll($partials);
    }

    public function mergeExcludedResolvedPartials(ResolvedPartialsCollection $partials): void
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new ResolvedPartialsCollection();
        }

        $this->excludePartials->addAll($partials);
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
        if ($this->includePartials !== null) {
            foreach ($this->includePartials as $includedPartial) {
                $includedPartial->rollbackWhenRequired();
            }
        }

        if ($this->excludePartials !== null) {
            foreach ($this->excludePartials as $excludePartial) {
                $excludePartial->rollbackWhenRequired();
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

    /**
     * @param IncludeableData&(BaseData|BaseDataCollectable) $data
     */
    public function mergePartialsFromDataContext(
        IncludeableData $data
    ): self {
        $dataContext = $data->getDataContext();

        if ($dataContext->includePartials && $dataContext->includePartials->count() > 0) {
            $this->mergeIncludedResolvedPartials(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($data, $dataContext->includePartials)
            );
        }

        if ($dataContext->excludePartials && $dataContext->excludePartials->count() > 0) {
            $this->mergeExcludedResolvedPartials(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($data, $dataContext->excludePartials)
            );
        }

        if ($dataContext->onlyPartials && $dataContext->onlyPartials->count() > 0) {
            $this->mergeOnlyResolvedPartials(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($data, $dataContext->onlyPartials)
            );
        }

        if ($dataContext->exceptPartials && $dataContext->exceptPartials->count() > 0) {
            $this->mergeExceptResolvedPartials(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($data, $dataContext->exceptPartials)
            );
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'transformValues' => $this->transformValues,
            'mapPropertyNames' => $this->mapPropertyNames,
            'wrapExecutionType' => $this->wrapExecutionType,
            'transformers' => $this->transformers !== null ? iterator_to_array($this->transformers) : null,
            'includePartials' => $this->includePartials?->toArray(),
            'excludePartials' => $this->excludePartials?->toArray(),
            'onlyPartials' => $this->onlyPartials?->toArray(),
            'exceptPartials' => $this->exceptPartials?->toArray(),
        ];
    }

    public function __clone(): void
    {
        if ($this->includePartials !== null) {
            $this->includePartials = clone $this->includePartials;
        }

        if ($this->excludePartials !== null) {
            $this->excludePartials = clone $this->excludePartials;
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

        if ($this->includePartials !== null && $this->includePartials->count() > 0) {
            $output .= $this->includePartials;
        }

        if ($this->excludePartials !== null && $this->excludePartials->count() > 0) {
            $output .= $this->excludePartials;
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
