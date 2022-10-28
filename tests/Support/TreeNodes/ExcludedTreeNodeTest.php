<?php

use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;

it('can merge a node', function () {
    $node = new ExcludedTreeNode();

    expect($node->merge(new AllTreeNode()))->toEqual(new AllTreeNode())
        ->and($node->merge(new ExcludedTreeNode()))->toEqual($node)
        ->and($node->merge(new DisabledTreeNode()))
        ->toEqual(new DisabledTreeNode())
        ->and($node->merge(new PartialTreeNode()))
        ->toEqual(new PartialTreeNode())
        ->and(
            $node->merge(new PartialTreeNode(['nested' => new ExcludedTreeNode()]))
        )
        ->toEqual(new PartialTreeNode(['nested' => new ExcludedTreeNode()]));
});

it('can intersect a node', function () {
    $node = new ExcludedTreeNode();

    expect($node->intersect(new AllTreeNode()))->toEqual($node)
        ->and($node->intersect(new ExcludedTreeNode()))->toEqual($node)
        ->and($node->intersect(new DisabledTreeNode()))->toEqual($node)
        ->and($node->intersect(new PartialTreeNode()))->toEqual($node)
        ->and(
            $node->intersect(new PartialTreeNode(['nested' => new ExcludedTreeNode()]))
        )->toEqual($node);
});
