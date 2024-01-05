<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use SplObjectStorage;

class DataContext
{
    /**
     * @param SplObjectStorage<Partial> $includePartials
     * @param SplObjectStorage<Partial> $excludePartials
     * @param SplObjectStorage<Partial> $onlyPartials
     * @param SplObjectStorage<Partial> $exceptPartials
     */
    public function __construct(
        public SplObjectStorage $includePartials,
        public SplObjectStorage $excludePartials,
        public SplObjectStorage $onlyPartials,
        public SplObjectStorage $exceptPartials,
        public ?Wrap $wrap = null,
    ) {
    }

    public function mergePartials(DataContext $dataContext): self
    {
        $this->includePartials->addAll($dataContext->includePartials);
        $this->excludePartials->addAll($dataContext->excludePartials);
        $this->onlyPartials->addAll($dataContext->onlyPartials);
        $this->exceptPartials->addAll($dataContext->exceptPartials);

        return $this;
    }

    /**
     * @param SplObjectStorage<Partial> $partials
     *
     * @return SplObjectStorage<ResolvedPartial>
     */
    public function getResolvedPartialsAndRemoveTemporaryOnes(
        BaseData|BaseDataCollectable $data,
        SplObjectStorage $partials,
    ): SplObjectStorage {
        $resolvedPartials = new SplObjectStorage();
        $partialsToDetach = new SplObjectStorage();

        foreach ($partials as $partial) {
            if ($resolved = $partial->resolve($data)) {
                $resolvedPartials->attach($resolved);
            }

            if (! $partial->permanent) {
                $partialsToDetach->attach($partial);
            }
        }

        $partials->removeAll($partialsToDetach);

        return $resolvedPartials;
    }
}
