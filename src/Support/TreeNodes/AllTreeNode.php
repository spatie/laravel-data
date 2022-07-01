<?php

namespace Spatie\LaravelData\Support\TreeNodes;

class AllTreeNode implements TreeNode
{
    public function merge(TreeNode $other): self
    {
        return new self();
    }

    public function intersect(TreeNode $other): TreeNode
    {
        return $other;
    }

    public function getNested(string $field): self
    {
        return new self();
    }

    public function __toString(): string
    {
        return '{"*"}';
    }

    public function getFields(): array
    {
        return ['*'];
    }
}
