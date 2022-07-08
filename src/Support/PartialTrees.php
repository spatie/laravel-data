<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Stringable;

class PartialTrees implements Stringable
{
    public function __construct(
        public TreeNode $lazyIncluded = new DisabledTreeNode(),
        public TreeNode $lazyExcluded = new DisabledTreeNode(),
        public TreeNode $only = new DisabledTreeNode(),
        public TreeNode $except = new DisabledTreeNode(),
    ) {
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

    public function __toString()
    {
        return '{' . PHP_EOL . collect([
                'lazyIncluded' => $this->lazyIncluded,
                'lazyExcluded' => $this->lazyExcluded,
                'only' => $this->only,
                'except' => $this->except,
            ])
                ->map(fn (TreeNode $node, string $type) => "\"{$type}\":{$node}")
                ->join(',' . PHP_EOL) . PHP_EOL . '}';
    }
}
