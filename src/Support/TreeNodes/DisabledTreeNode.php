<?php

namespace Spatie\LaravelData\Support\TreeNodes;

class DisabledTreeNode implements TreeNode
{
    public function merge(TreeNode $other): TreeNode
    {
        return $other;
    }

    public function intersect(TreeNode $other): TreeNode
    {
        return $this;
    }

    public function getNested(string $field): self
    {
        return new self();
    }

    public function __toString()
    {
        return '{}';
    }

    public function getFields(): array
    {
        return [];
    }
}
