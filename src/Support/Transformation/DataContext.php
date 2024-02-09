<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
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
}
