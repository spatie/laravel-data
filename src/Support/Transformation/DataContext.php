<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Wrapping\Wrap;

class DataContext
{
    public function __construct(
        public ?PartialsCollection $includePartials,
        public ?PartialsCollection $excludePartials,
        public ?PartialsCollection $onlyPartials,
        public ?PartialsCollection $exceptPartials,
        public ?Wrap $wrap = null,
    ) {
    }

    public function mergePartials(DataContext $dataContext): self
    {
        if ($dataContext->includePartials) {
            $this->includePartials ??= new PartialsCollection();

            $this->includePartials->addAll($dataContext->includePartials);
        }

        if ($dataContext->excludePartials) {
            $this->excludePartials ??= new PartialsCollection();

            $this->excludePartials->addAll($dataContext->excludePartials);
        }

        if ($dataContext->onlyPartials) {
            $this->onlyPartials ??= new PartialsCollection();

            $this->onlyPartials->addAll($dataContext->onlyPartials);
        }

        if ($dataContext->exceptPartials) {
            $this->exceptPartials ??= new PartialsCollection();

            $this->exceptPartials->addAll($dataContext->exceptPartials);
        }

        return $this;
    }

    public function mergeTransformationContext(
        TransformationContext $context,
    ): self {
        $decoupledPartialResolver = DataContainer::get()->decoupledPartialResolver();

        if ($context->includePartials) {
            $this->includePartials ??= new PartialsCollection();

            foreach ($context->includePartials as $partial) {
                $partial = $decoupledPartialResolver->execute($partial);

                if($partial !== null) {
                    $this->includePartials->attach($partial);
                }
            }
        }

        if ($context->excludePartials) {
            $this->excludePartials ??= new PartialsCollection();

            foreach ($context->excludePartials as $partial) {
                $partial = $decoupledPartialResolver->execute($partial);

                if($partial !== null) {
                    $this->excludePartials->attach($partial);
                }
            }
        }

        if ($context->onlyPartials) {
            $this->onlyPartials ??= new PartialsCollection();

            foreach ($context->onlyPartials as $partial) {
                $partial = $decoupledPartialResolver->execute($partial);

                if($partial !== null) {
                    $this->onlyPartials->attach($partial);
                }
            }
        }

        if ($context->exceptPartials) {
            $this->exceptPartials ??= new PartialsCollection();

            foreach ($context->exceptPartials as $partial) {
                $partial = $decoupledPartialResolver->execute($partial);

                if($partial !== null) {
                    $this->exceptPartials->attach($partial);
                }
            }
        }

        return $this;
    }

    public function getRequiredPartialsAndRemoveTemporaryOnes(
        BaseData|BaseDataCollectable $data,
        PartialsCollection $partials,
    ): PartialsCollection {
        $requiredPartials = new PartialsCollection();
        $partialsToDetach = new PartialsCollection();

        foreach ($partials as $partial) {
            if ($partial->isRequired($data)) {
                $requiredPartials->attach($partial->reset());
            }

            if (! $partial->permanent) {
                $partialsToDetach->attach($partial);
            }
        }

        $partials->removeAll($partialsToDetach);

        return $requiredPartials;
    }

    public function toSerializedArray(): array
    {
        return [
            'includePartials' => $this->includePartials?->toSerializedArray(),
            'excludePartials' => $this->excludePartials?->toSerializedArray(),
            'onlyPartials' => $this->onlyPartials?->toSerializedArray(),
            'exceptPartials' => $this->exceptPartials?->toSerializedArray(),
            'wrap' => $this->wrap?->toSerializedArray(),
        ];
    }

    public static function fromSerializedArray(array $content): DataContext
    {
        return new self(
            includePartials: $content['includePartials']
                ? PartialsCollection::fromSerializedArray($content['includePartials'])
                : null,
            excludePartials: $content['excludePartials']
                ? PartialsCollection::fromSerializedArray($content['excludePartials'])
                : null,
            onlyPartials: $content['onlyPartials']
                ? PartialsCollection::fromSerializedArray($content['onlyPartials'])
                : null,
            exceptPartials: $content['exceptPartials']
                ? PartialsCollection::fromSerializedArray($content['exceptPartials'])
                : null,
            wrap: $content['wrap']
                ? Wrap::fromSerializedArray($content['wrap'])
                : null,
        );
    }
}
