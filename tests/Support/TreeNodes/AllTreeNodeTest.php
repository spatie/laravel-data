<?php

use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;

it('can merge a node', function () {
    $node = new AllTreeNode();

    expect($node)->toEqual($node->merge(new AllTreeNode()))
        ->toEqual($node->merge(new ExcludedTreeNode()))
        ->toEqual($node->merge(new DisabledTreeNode()))
        ->toEqual($node->merge(new PartialTreeNode()))
        ->toEqual($node->merge(new PartialTreeNode()))
        ->toEqual($node->merge(new PartialTreeNode([
            'nested' => new ExcludedTreeNode()
        ])));
});

it('can intersect a node', function () {
    $node = new AllTreeNode();

    expect($node)->toEqual($node->intersect(new AllTreeNode()))
        ->and($node->intersect(new ExcludedTreeNode()))->toEqual(new ExcludedTreeNode())
        ->and($node->intersect(new DisabledTreeNode()))->toEqual(new DisabledTreeNode())
        ->and($node->intersect(new PartialTreeNode()))->toEqual(new PartialTreeNode())
        ->and($node->intersect(new ExcludedTreeNode()))->toEqual(new ExcludedTreeNode())
        ->and(
            $node->intersect(
                new PartialTreeNode(['nested' => new ExcludedTreeNode()])
            )
        )->toEqual(new PartialTreeNode(['nested' => new ExcludedTreeNode()]));
});
