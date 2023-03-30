<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Str;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;

class PartialsParser
{
    public function execute(array $partials, ?PartialTreesMapping $mapping = null): TreeNode
    {
        $nodes = new DisabledTreeNode();

        foreach ($partials as $directive) {
            $directive = str_replace(' ', '', $directive);

            $nested = str_contains($directive, '.') ? Str::after($directive, '.') : null;
            $field = Str::before($directive, '.');

            if ($field === '*') {
                return new AllTreeNode();
            }

            if (Str::startsWith($field, '{') && Str::endsWith($field, '}')) {
                $children = collect(explode(',', substr($field, 1, -1)))
                    ->values()
                    ->map(fn (string $child) => $mapping?->getChild($child)?->original ?? $child)
                    ->flip()
                    ->map(fn () => new ExcludedTreeNode())
                    ->all();

                $nodes = $nodes->merge(new PartialTreeNode($children));

                continue;
            }

            $mappingForField = $mapping?->getChild($field);

            $nestedNode = $nested === null
                ? new ExcludedTreeNode()
                : $this->execute([$nested], $mappingForField);

            $fieldName = $mappingForField?->original ?? $field;

            $nodes = $nodes->merge(new PartialTreeNode([
                $fieldName => $nestedNode,
            ]));
        }

        return $nodes;
    }
}
