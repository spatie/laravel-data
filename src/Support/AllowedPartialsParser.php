<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;

class AllowedPartialsParser
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(
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
                        $field => $this->execute(
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
