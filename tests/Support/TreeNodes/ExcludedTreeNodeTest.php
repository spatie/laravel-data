<?php

namespace Spatie\LaravelData\Tests\Support\TreeNodes;

use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Tests\TestCase;

it('can merge a node', function () {
    $node = new ExcludedTreeNode();

    expect($node->merge(new AllTreeNode()))
        ->toEqual(new AllTreeNode());

    expect($node->merge(new ExcludedTreeNode()))
        ->toEqual($node);

    expect($node->merge(new DisabledTreeNode()))
        ->toEqual(new DisabledTreeNode());

    expect($node->merge(new PartialTreeNode()))
        ->toEqual(new PartialTreeNode());

    expect(
        $node->merge(new PartialTreeNode(['nested' => new ExcludedTreeNode()]))
    )
        ->toEqual(new PartialTreeNode(['nested' => new ExcludedTreeNode()]));
});

it('can intersect a node', function () {
    $node = new ExcludedTreeNode();

    expect($node->intersect(new AllTreeNode()))
        ->toEqual($node);

    expect($node->intersect(new ExcludedTreeNode()))
        ->toEqual($node);

    expect($node->intersect(new DisabledTreeNode()))
        ->toEqual($node);

    expect($node->intersect(new PartialTreeNode()))
        ->toEqual($node);

    expect($node->intersect(new PartialTreeNode(['nested' => new ExcludedTreeNode()])))
        ->toEqual($node);
});
