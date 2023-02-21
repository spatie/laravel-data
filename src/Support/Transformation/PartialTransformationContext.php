<?php

namespace Spatie\LaravelData\Support\Transformation;

use Closure;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class PartialTransformationContext
{
    public function __construct(
        public TreeNode $lazyIncluded = new DisabledTreeNode(),
        public TreeNode $lazyExcluded = new DisabledTreeNode(),
        public TreeNode $only = new DisabledTreeNode(),
        public TreeNode $except = new DisabledTreeNode(),
    ) {
    }

    public static function create(
        BaseData|BaseDataCollectable $data,
        PartialsDefinition $partialsDefinition,
    ): self {
        $filter = fn(bool|null|Closure $condition, string $definition) => match (true) {
            is_bool($condition) => $condition,
            $condition === null => false,
            is_callable($condition) => $condition($data),
        };

        return new self(
            app(PartialsParser::class)->execute(
                collect($partialsDefinition->includes)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($partialsDefinition->excludes)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($partialsDefinition->only)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($partialsDefinition->except)->filter($filter)->keys()->all()
            ),
        );
    }

    public function merge(self $other): self
    {
        return new self(
            $this->lazyIncluded->merge($other->lazyIncluded),
            $this->lazyExcluded->merge($other->lazyExcluded),
            $this->only->merge($other->only),
            $this->except->merge($other->except),
        );
    }

    public function getNested(string $field): self
    {
        return new self(
            $this->lazyIncluded->getNested($field),
            $this->lazyExcluded->getNested($field),
            $this->only->getNested($field),
            $this->except->getNested($field),
        );
    }
}
