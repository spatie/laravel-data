<?php

namespace Spatie\LaravelData\Support\Transformation;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;

class PartialTransformationContext
{
    public function __construct(
        public TreeNode $lazyIncluded = new DisabledTreeNode(),
        public TreeNode $lazyExcluded = new DisabledTreeNode(),
        public TreeNode $only = new DisabledTreeNode(),
        public TreeNode $except = new DisabledTreeNode(),
    ) {

    }

    public static function create(
        BaseData|BaseDataCollectable $data,
        PartialsDefinition $partialsDefinition,
    ): self {
        return new self(
            DataContainer::get()->partialsParser()->execute(
                static::filterDefinitions($data, $partialsDefinition->includes),
            ),
            DataContainer::get()->partialsParser()->execute(
                static::filterDefinitions($data, $partialsDefinition->excludes),
            ),
            DataContainer::get()->partialsParser()->execute(
                static::filterDefinitions($data, $partialsDefinition->only),
            ),
            DataContainer::get()->partialsParser()->execute(
                static::filterDefinitions($data, $partialsDefinition->except),
            ),
        );
    }

    public function merge(self $other): self
    {
        return new self(
            $this->lazyIncluded->merge($other->lazyIncluded),
            $this->lazyExcluded->merge($other->lazyExcluded),
            $this->only->merge($other->only),
            $this->except->merge($other->except),
        );
    }

    public function getNested(string $field): self
    {
        return new self(
            $this->lazyIncluded->getNested($field),
            $this->lazyExcluded->getNested($field),
            $this->only->getNested($field),
            $this->except->getNested($field),
        );
    }

    private static function filterDefinitions(
        BaseData|BaseDataCollectable $data,
        array $definitions
    ): array {
        $filtered = [];

        foreach ($definitions as $definition => $condition) {
            if ($condition === true) {
                $filtered[] = $definition;
            }

            if (is_callable($condition) && $condition($data)) {
                $filtered[] = $definition;
            }
        }

        return $filtered;
    }
}
