<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
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
        public ?PartialsCollection $includePartials = null,
        public ?PartialsCollection $excludePartials = null,
        public ?PartialsCollection $onlyPartials = null,
        public ?PartialsCollection $exceptPartials = null,
        public int $depth = 0,
        public ?int $maxDepth = null,
        public bool $throwWhenMaxDepthReached = true,
    ) {
    }

    public function setWrapExecutionType(WrapExecutionType $wrapExecutionType): self
    {
        $this->wrapExecutionType = $wrapExecutionType;

        return $this;
    }

    public function addIncludedPartial(Partial ...$partials): void
    {
        if ($this->includePartials === null) {
            $this->includePartials = new PartialsCollection();
        }

        foreach ($partials as $partial) {
            $this->includePartials->attach($partial);
        }
    }

    public function addExcludedPartial(Partial ...$partials): void
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new PartialsCollection();
        }

        foreach ($partials as $partial) {
            $this->excludePartials->attach($partial);
        }
    }

    public function addOnlyPartial(Partial ...$partials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new PartialsCollection();
        }

        foreach ($partials as $partial) {
            $this->onlyPartials->attach($partial);
        }
    }

    public function addExceptPartial(Partial ...$partials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new PartialsCollection();
        }

        foreach ($partials as $partial) {
            $this->exceptPartials->attach($partial);
        }
    }

    public function mergeIncludedPartials(PartialsCollection $partials): void
    {
        if ($this->includePartials === null) {
            $this->includePartials = new PartialsCollection();
        }

        $this->includePartials->addAll($partials);
    }

    public function mergeExcludedPartials(PartialsCollection $partials): void
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new PartialsCollection();
        }

        $this->excludePartials->addAll($partials);
    }

    public function mergeOnlyPartials(PartialsCollection $partials): void
    {
        if ($this->onlyPartials === null) {
            $this->onlyPartials = new PartialsCollection();
        }

        $this->onlyPartials->addAll($partials);
    }

    public function mergeExceptPartials(PartialsCollection $partials): void
    {
        if ($this->exceptPartials === null) {
            $this->exceptPartials = new PartialsCollection();
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
            $this->mergeIncludedPartials(
                $dataContext->getRequiredPartialsAndRemoveTemporaryOnes($data, $dataContext->includePartials)
            );
        }

        if ($dataContext->excludePartials && $dataContext->excludePartials->count() > 0) {
            $this->mergeExcludedPartials(
                $dataContext->getRequiredPartialsAndRemoveTemporaryOnes($data, $dataContext->excludePartials)
            );
        }

        if ($dataContext->onlyPartials && $dataContext->onlyPartials->count() > 0) {
            $this->mergeOnlyPartials(
                $dataContext->getRequiredPartialsAndRemoveTemporaryOnes($data, $dataContext->onlyPartials)
            );
        }

        if ($dataContext->exceptPartials && $dataContext->exceptPartials->count() > 0) {
            $this->mergeExceptPartials(
                $dataContext->getRequiredPartialsAndRemoveTemporaryOnes($data, $dataContext->exceptPartials)
            );
        }

        return $this;
    }

    public function hasPartials(): bool
    {
        if ($this->includePartials !== null && $this->includePartials->count() > 0) {
            return true;
        }

        if ($this->excludePartials !== null && $this->excludePartials->count() > 0) {
            return true;
        }

        if ($this->onlyPartials !== null && $this->onlyPartials->count() > 0) {
            return true;
        }

        if ($this->exceptPartials !== null && $this->exceptPartials->count() > 0) {
            return true;
        }

        return false;
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
            $this->includePartials = $this->includePartials->manualClone();
        }

        if ($this->excludePartials !== null) {
            $this->excludePartials = $this->excludePartials->manualClone();
        }

        if ($this->onlyPartials !== null) {
            $this->onlyPartials = $this->onlyPartials->manualClone();
        }

        if ($this->exceptPartials !== null) {
            $this->exceptPartials = $this->exceptPartials->manualClone();
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
            $output .= "- include partials:".PHP_EOL;
            $output .= $this->includePartials;
        }

        if ($this->excludePartials !== null && $this->excludePartials->count() > 0) {
            $output .= "- exclude partials:".PHP_EOL;
            $output .= $this->excludePartials;
        }

        if ($this->onlyPartials !== null && $this->onlyPartials->count() > 0) {
            $output .= "- only partials:".PHP_EOL;
            $output .= $this->onlyPartials;
        }

        if ($this->exceptPartials !== null && $this->exceptPartials->count() > 0) {
            $output .= "- except partials:".PHP_EOL;
            $output .= $this->exceptPartials;
        }

        return $output;
    }
}
