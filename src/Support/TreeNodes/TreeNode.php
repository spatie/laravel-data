<?php

namespace Spatie\LaravelData\Support\TreeNodes;

use Stringable;

interface TreeNode extends Stringable
{
    public function merge(TreeNode $other): TreeNode;

    public function intersect(TreeNode $other): TreeNode;

    public function getNested(string $field): TreeNode;

    public function getFields(): array;
}
