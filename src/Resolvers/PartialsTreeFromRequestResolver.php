<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataObject;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;

class PartialsTreeFromRequestResolver
{
    public function __construct(
        private DataConfig $dataConfig,
        private PartialsParser $partialsParser,
    ) {
    }

    public function execute(
        DataObject|DataCollection|PaginatedDataCollection $data,
        Request $request,
    ): PartialTrees {
        $includesTree = $this->partialsParser->execute(explode(',', $request->get('include', '')));
        $excludesTree = $this->partialsParser->execute(explode(',', $request->get('exclude', '')));
        $onlyTree = $this->partialsParser->execute(explode(',', $request->get('only', '')));
        $exceptTree = $this->partialsParser->execute(explode(',', $request->get('except', '')));

        $dataClass = $data instanceof DataObject
            ? $data::class
            : $data->dataClass;

        return new PartialTrees(
            $request->has('include') ? $this->getValidIncludesForDataClass($dataClass, $includesTree) : null,
            $request->has('exclude') ? $this->getValidExcludesForDataClass($dataClass, $excludesTree) : null,
            $request->has('only') ? $this->getValidOnlyForDataClass($dataClass, $onlyTree) : null,
            $request->has('except') ? $this->getValidExceptForDataClass($dataClass, $exceptTree) : null,
        );
    }

    private function getValidIncludesForDataClass(
        string $class,
        array $requestedPartialsTree
    ): array {
        return $this->reducePartialsTree(
            $class,
            $requestedPartialsTree,
            $class::allowedRequestIncludes(),
            __FUNCTION__
        );
    }

    private function getValidExcludesForDataClass(
        string $class,
        array $requestedPartialsTree
    ): array {
        return $this->reducePartialsTree(
            $class,
            $requestedPartialsTree,
            $class::allowedRequestExcludes(),
            __FUNCTION__
        );
    }

    private function getValidOnlyForDataClass(
        string $class,
        array $requestedPartialsTree
    ): array {
        return $this->reducePartialsTree(
            $class,
            $requestedPartialsTree,
            $class::allowedRequestOnly(),
            __FUNCTION__
        );
    }

    private function getValidExceptForDataClass(
        string $class,
        array $requestedPartialsTree
    ): array {
        return $this->reducePartialsTree(
            $class,
            $requestedPartialsTree,
            $class::allowedRequestExcept(),
            __FUNCTION__
        );
    }

    private function reducePartialsTree(
        string $class,
        array $requestedPartialsTree,
        ?array $allowedPartials,
        string $methodName,
    ): array {
        $properties = $this->dataConfig->getDataClass($class)->properties;

        if ($allowedPartials === null) {
            return $requestedPartialsTree;
        }

        if ($requestedPartialsTree === ['*']) {
            return [];
        }

        foreach ($requestedPartialsTree as $requestedPartial => $nested) {
            if (! in_array($requestedPartial, $allowedPartials)) {
                unset($requestedPartialsTree[$requestedPartial]);

                continue;
            }

            if (! $properties->has($requestedPartial)) {
                continue;
            }

            $checkNested = $properties[$requestedPartial]->type->isDataObject
                || $properties[$requestedPartial]->type->isDataCollection;

            if ($checkNested) {
                $requestedPartialsTree[$requestedPartial] = $this->{$methodName}(
                    $properties[$requestedPartial]->type->dataClass,
                    $nested
                );
            }
        }

        return $requestedPartialsTree;
    }
}
