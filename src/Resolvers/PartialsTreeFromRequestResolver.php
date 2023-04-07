<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\AllowedPartialsParser;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\Transformation\PartialTransformationContext;
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
        BaseData|BaseDataCollectable $data,
        Request $request,
    ): PartialTransformationContext {
        $dataClass = $this->dataConfig->getDataClass(match (true) {
            $data instanceof BaseData => $data::class,
            $data instanceof BaseDataCollectable => $data->getDataClass(),
            default => throw new TypeError('Invalid type of data')
        });

        $mapping = $dataClass->outputNameMapping->resolve();

        $requestedIncludesTree = $this->partialsParser->execute(
            $request->has('include') ? $this->arrayFromRequest($request, 'include') : [],
            $mapping
        );
        $requestedExcludesTree = $this->partialsParser->execute(
            $request->has('exclude') ? $this->arrayFromRequest($request, 'exclude') : [],
            $mapping
        );
        $requestedOnlyTree = $this->partialsParser->execute(
            $request->has('only') ? $this->arrayFromRequest($request, 'only') : [],
            $mapping
        );
        $requestedExceptTree = $this->partialsParser->execute(
            $request->has('except') ? $this->arrayFromRequest($request, 'except') : [],
            $mapping
        );

        $allowedRequestIncludesTree = $this->allowedPartialsParser->execute('allowedRequestIncludes', $dataClass);
        $allowedRequestExcludesTree = $this->allowedPartialsParser->execute('allowedRequestExcludes', $dataClass);
        $allowedRequestOnlyTree = $this->allowedPartialsParser->execute('allowedRequestOnly', $dataClass);
        $allowedRequestExceptTree = $this->allowedPartialsParser->execute('allowedRequestExcept', $dataClass);

        return new PartialTransformationContext(
            $requestedIncludesTree->intersect($allowedRequestIncludesTree),
            $requestedExcludesTree->intersect($allowedRequestExcludesTree),
            $requestedOnlyTree->intersect($allowedRequestOnlyTree),
            $requestedExceptTree->intersect($allowedRequestExceptTree)
        );
    }

    protected function arrayFromRequest(Request $request, string $key): array
    {
        $value = $request->get($key);

        return is_array($value) ? $value : explode(',', $value);
    }
}
