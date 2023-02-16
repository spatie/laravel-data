<?php

namespace Spatie\LaravelData\Support\Transforming;

use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class TransformationContext
{
    public function __construct(
        public readonly bool $transformValues,
        public readonly bool $mapPropertyNames,
        public readonly ?Wrap $wrap,
        public readonly TreeNode $lazyIncluded,
        public readonly TreeNode $lazyExcluded,
        public readonly TreeNode $only,
        public readonly TreeNode $except,
    )
    {
    }
}
