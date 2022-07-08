<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Support\AllowedPartialsParser;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use TypeError;

class PartialsTreeFromRequestResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected PartialsParser $partialsParser,
        protected AllowedPartialsParser $allowedPartialsParser,
    ) {
    }

    public function execute(
        IncludeableData $data,
        Request $request,
    ): PartialTrees {
        $requestedIncludesTree = $this->partialsParser->execute(
            $request->has('include') ? explode(',', $request->get('include')) : []
        );
        $requestedExcludesTree = $this->partialsParser->execute(
            $request->has('exclude') ? explode(',', $request->get('exclude')) : []
        );
        $requestedOnlyTree = $this->partialsParser->execute(
            $request->has('only') ? explode(',', $request->get('only')) : []
        );
        $requestedExceptTree = $this->partialsParser->execute(
            $request->has('except') ? explode(',', $request->get('except')) : []
        );

        $dataClass = match (true) {
            $data instanceof BaseData => $data::class,
            $data instanceof BaseDataCollectable => $data->getDataClass(),
            default => throw new TypeError('Invalid type of data')
        };

        $allowedRequestIncludesTree = $this->allowedPartialsParser->execute('allowedRequestIncludes', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestExcludesTree = $this->allowedPartialsParser->execute('allowedRequestExcludes', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestOnlyTree = $this->allowedPartialsParser->execute('allowedRequestOnly', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestExceptTree = $this->allowedPartialsParser->execute('allowedRequestExcept', $this->dataConfig->getDataClass($dataClass));

        $partialTrees = $data->getPartialTrees();

        return new PartialTrees(
            $partialTrees->lazyIncluded->merge($requestedIncludesTree->intersect($allowedRequestIncludesTree)),
            $partialTrees->lazyExcluded->merge($requestedExcludesTree->intersect($allowedRequestExcludesTree)),
            $partialTrees->only->merge($requestedOnlyTree->intersect($allowedRequestOnlyTree)),
            $partialTrees->except->merge($requestedExceptTree->intersect($allowedRequestExceptTree))
        );
    }
}
