<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Support\AllowedPartialsParser;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;
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
        $dataClass = match (true) {
            $data instanceof BaseData => $data::class,
            $data instanceof BaseDataCollectable => $data->getDataClass(),
            default => throw new TypeError('Invalid type of data')
        };

        $dataClass = $this->dataConfig->getDataClass($dataClass);

        $requestedIncludesTree = $this->partialsParser->execute(
            $request->has('include') ? $this->arrayFromRequest($request, 'include') : [],
            $dataClass->outputNameMapping
        );
        $requestedExcludesTree = $this->partialsParser->execute(
            $request->has('exclude') ? $this->arrayFromRequest($request, 'exclude') : [],
            $dataClass->outputNameMapping
        );
        $requestedOnlyTree = $this->partialsParser->execute(
            $request->has('only') ? $this->arrayFromRequest($request, 'only') : [],
            $dataClass->outputNameMapping
        );
        $requestedExceptTree = $this->partialsParser->execute(
            $request->has('except') ? $this->arrayFromRequest($request, 'except') : [],
            $dataClass->outputNameMapping
        );

        $allowedRequestIncludesTree = $this->allowedPartialsParser->execute('allowedRequestIncludes', $dataClass);
        $allowedRequestExcludesTree = $this->allowedPartialsParser->execute('allowedRequestExcludes', $dataClass);
        $allowedRequestOnlyTree = $this->allowedPartialsParser->execute('allowedRequestOnly', $dataClass);
        $allowedRequestExceptTree = $this->allowedPartialsParser->execute('allowedRequestExcept', $dataClass);

        $partialTrees = $data->getPartialTrees();

        return new PartialTrees(
            $partialTrees->lazyIncluded->merge($requestedIncludesTree->intersect($allowedRequestIncludesTree)),
            $partialTrees->lazyExcluded->merge($requestedExcludesTree->intersect($allowedRequestExcludesTree)),
            $partialTrees->only->merge($requestedOnlyTree->intersect($allowedRequestOnlyTree)),
            $partialTrees->except->merge($requestedExceptTree->intersect($allowedRequestExceptTree))
        );
    }

    protected function arrayFromRequest(Request $request, string $key): array
    {
        $value = $request->get($key);

        return is_array($value) ? $value : explode(',', $value);
    }
}
