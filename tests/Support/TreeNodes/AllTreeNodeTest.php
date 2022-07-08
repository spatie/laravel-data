<?php

namespace Spatie\LaravelData\Tests\Support\TreeNodes;

use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Tests\TestCase;

class AllTreeNodeTest extends TestCase
{
    /** @test */
    public function it_can_merge_a_node()
    {
        $node = new AllTreeNode();

        $this->assertEquals(
            $node,
            $node->merge(new AllTreeNode())
        );

        $this->assertEquals(
            $node,
            $node->merge(new ExcludedTreeNode())
        );

        $this->assertEquals(
            $node,
            $node->merge(new DisabledTreeNode())
        );

        $this->assertEquals(
            $node,
            $node->merge(new PartialTreeNode())
        );

        $this->assertEquals(
            $node,
            $node->merge(new PartialTreeNode(['nested' => new ExcludedTreeNode()]))
        );
    }

    /** @test */
    public function it_can_intersect_a_node()
    {
        $node = new AllTreeNode();

        $this->assertEquals(
            $node,
            $node->intersect(new AllTreeNode())
        );

        $this->assertEquals(
            new ExcludedTreeNode(),
            $node->intersect(new ExcludedTreeNode())
        );

        $this->assertEquals(
            new DisabledTreeNode(),
            $node->intersect(new DisabledTreeNode())
        );

        $this->assertEquals(
            new PartialTreeNode(),
            $node->intersect(new PartialTreeNode())
        );

        $this->assertEquals(
            new PartialTreeNode(['nested' => new ExcludedTreeNode()]),
            $node->intersect(new PartialTreeNode(['nested' => new ExcludedTreeNode()]))
        );
    }
}
