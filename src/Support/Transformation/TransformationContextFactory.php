<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\Transformer;

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
        public ?GlobalTransformersCollection $transformers = null,
        public ?PartialsCollection $includePartials = null,
        public ?PartialsCollection $excludePartials = null,
        public ?PartialsCollection $onlyPartials = null,
        public ?PartialsCollection $exceptPartials = null,
    ) {
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

    public function transformer(string $transformable, Transformer $transformer): static
    {
        if ($this->transformers === null) {
            $this->transformers = new GlobalTransformersCollection();
        }

        $this->transformers->add($transformable, $transformer);

        return $this;
    }

    public function addIncludePartial(Partial ...$partial): static
    {
        if ($this->includePartials === null) {
            $this->includePartials = new PartialsCollection();
        }

        foreach ($partial as $include) {
            $this->includePartials->attach($include);
        }

        return $this;
    }

    public function addExcludePartial(Partial ...$partial): static
    {
        if ($this->excludePartials === null) {
            $this->excludePartials = new PartialsCollection();
        }

        foreach ($partial as $exclude) {
            $this->excludePartials->attach($exclude);
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
