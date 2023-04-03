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
        $requestedIncludesTree = $this->partialsParser->execute(
            $request->has('include') ? $this->arrayFromRequest($request, 'include') : []
        );
        $requestedExcludesTree = $this->partialsParser->execute(
            $request->has('exclude') ? $this->arrayFromRequest($request, 'exclude') : []
        );
        $requestedOnlyTree = $this->partialsParser->execute(
            $request->has('only') ? $this->arrayFromRequest($request, 'only') : []
        );
        $requestedExceptTree = $this->partialsParser->execute(
            $request->has('except') ? $this->arrayFromRequest($request, 'except') : []
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

    protected function arrayFromRequest(Request $request, string $key): array
    {
        $value = $request->get($key);

        return is_array($value) ? $value : explode(',', $value);
    }
}
