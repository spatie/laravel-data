<?php

namespace Spatie\LaravelData\Support\TreeNodes;

use TypeError;

class PartialTreeNode implements TreeNode
{
    public function __construct(
        /** @var Array<string, \Spatie\LaravelData\Support\TreeNodes\TreeNode> */
        protected array $children = []
    ) {
    }

    public function merge(TreeNode $other): TreeNode
    {
        if ($other instanceof AllTreeNode) {
            return $other;
        }

        if ($other instanceof DisabledTreeNode || $other instanceof ExcludedTreeNode) {
            return $this;
        }

        if (! $other instanceof PartialTreeNode) {
            throw new TypeError('Invalid node type');
        }

        $children = $this->children;

        foreach ($other->children as $otherField => $otherNode) {
            if (array_key_exists($otherField, $children)) {
                $children[$otherField] = $otherNode->merge($children[$otherField]);

                continue;
            }

            $children[$otherField] = $otherNode;
        }

        return new PartialTreeNode($children);
    }

    public function intersect(TreeNode $other): TreeNode
    {
        if ($other instanceof AllTreeNode) {
            return $this;
        }

        if ($other instanceof DisabledTreeNode || $other instanceof ExcludedTreeNode) {
            return $other;
        }

        if ($other instanceof PartialTreeNode) {
            $children = [];

            foreach ($other->children as $otherField => $otherNode) {
                if (array_key_exists($otherField, $this->children)) {
                    $children[$otherField] = $this->children[$otherField]->intersect($otherNode);
                }
            }

            return new PartialTreeNode($children);
        }

        throw new TypeError('Unknown tree node type');
    }

    public function getNested(string $field): TreeNode
    {
        return $this->children[$field] ?? new ExcludedTreeNode();
    }

    public function hasField(string $field): bool
    {
        return array_key_exists($field, $this->children);
    }

    public function __toString(): string
    {
        return '{' . collect($this->children)->map(fn (TreeNode $child, string $field) => "\"{$field}\":{$child}")->join(',') . '}';
    }

    public function getFields(): array
    {
        return array_keys($this->children);
    }
}
