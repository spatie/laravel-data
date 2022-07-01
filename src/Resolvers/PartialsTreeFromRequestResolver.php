<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
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
        private DataConfig $dataConfig,
        private PartialsParser $partialsParser,
    ) {
    }

    public function execute(
        IncludeableData $data,
        Request $request,
    ): PartialTrees {
        // Create a partial tree from the request includes and do an intersection with the allowed request includes
        // Then merge with the manually defined includes

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

        $allowedRequestIncludesTree = $this->buildAllowedPartialsTree('allowedRequestIncludes', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestExcludesTree = $this->buildAllowedPartialsTree('allowedRequestExcludes', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestOnlyTree = $this->buildAllowedPartialsTree('allowedRequestOnly', $this->dataConfig->getDataClass($dataClass));
        $allowedRequestExceptTree = $this->buildAllowedPartialsTree('allowedRequestExcept', $this->dataConfig->getDataClass($dataClass));

        $partialTrees = $data->getPartialTrees();

        return new PartialTrees(
            $partialTrees->lazyIncluded->merge($requestedIncludesTree->intersect($allowedRequestIncludesTree)),
            $partialTrees->lazyExcluded->merge($requestedExcludesTree->intersect($allowedRequestExcludesTree)),
            $partialTrees->only->merge($requestedOnlyTree->intersect($allowedRequestOnlyTree)),
            $partialTrees->except->merge($requestedExceptTree->intersect($allowedRequestExceptTree))
        );
    }

    private function buildAllowedPartialsTree(
        string $type,
        DataClass $dataClass
    ): TreeNode {
        $allowed = $dataClass->name::{$type}();

        if ($allowed === ['*'] || $allowed === null) {
            return new AllTreeNode();
        }

        $nodes = collect($allowed)
            ->filter(fn (string $field) => $dataClass->properties->has($field))
            ->mapWithKeys(function (string $field) use ($type, $dataClass) {
                /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
                $dataProperty = $dataClass->properties->get($field);

                if ($dataProperty->type->isDataObject || $dataProperty->type->isDataCollectable) {
                    return [
                        $field => $this->buildAllowedPartialsTree(
                            $type,
                            $this->dataConfig->getDataClass($dataProperty->type->dataClass)
                        ),
                    ];
                }

                return [$field => new ExcludedTreeNode()];
            });

        if ($nodes->isEmpty()) {
            return new ExcludedTreeNode();
        }

        return new PartialTreeNode($nodes->all());
    }
}
