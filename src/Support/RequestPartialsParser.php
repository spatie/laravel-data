<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Str;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;

class RequestPartialsParser
{
    protected array $outputNameMapping = [];

    public function setOutputNameMapping(array $outputNameMapping = []): self
    {
        $this->outputNameMapping = $outputNameMapping;

        return $this;
    }

    public function execute(array $partials, ?array $outputNameMapping = null): TreeNode
    {
        $nodes = new DisabledTreeNode();

        $outputNameMapping = $outputNameMapping ?? $this->outputNameMapping;

        foreach ($partials as $directive) {
            $directive = str_replace(' ', '', $directive);

            $nested = str_contains($directive, '.') ? Str::after($directive, '.') : null;
            $field = Str::before($directive, '.');

            $mappedProperty = $outputNameMapping[$field] ?? [];
            $mappedField = $mappedProperty['name'] ?? $field;

            $mappedPropertyChildren = $mappedProperty['children'] ?? [];

            if ($field === '*') {
                return new AllTreeNode();
            }

            if (Str::startsWith($field, '{') && Str::endsWith($field, '}')) {
                $children = collect(explode(',', substr($field, 1, -1)))
                    ->values()
                    ->map(function ($child) use ($outputNameMapping) {
                        return $outputNameMapping[$child]['name'] ?? $child;
                    })
                    ->flip()
                    ->map(fn () => new ExcludedTreeNode())
                    ->all();

                $nodes = $nodes->merge(new PartialTreeNode($children));

                continue;
            }

            $nestedNode = $nested === null || count($mappedPropertyChildren) === 0
                ? new ExcludedTreeNode()
                : $this->execute([$nested], $mappedPropertyChildren);

            $nodes = $nodes->merge(new PartialTreeNode([
                $mappedField => $nestedNode,
            ]));
        }

        return $nodes;
    }
}
