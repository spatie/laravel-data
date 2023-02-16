<?php

namespace Spatie\LaravelData\Support\Transformation;

use Closure;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class LocalTransformationContext
{
    public function __construct(
        public readonly TreeNode $lazyIncluded,
        public readonly TreeNode $lazyExcluded,
        public readonly TreeNode $only,
        public readonly TreeNode $except,
    ) {
    }

    public static function create(
        BaseData|BaseDataCollectable $data,
    ): self {
        $dataContext = $data->getDataContext();

        $filter = fn (bool|null|Closure $condition, string $definition) => match (true) {
            is_bool($condition) => $condition,
            $condition === null => false,
            is_callable($condition) => $condition($data),
        };

        return new self(
            app(PartialsParser::class)->execute(
                collect($dataContext->includes)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($dataContext->excludes)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($dataContext->only)->filter($filter)->keys()->all()
            ),
            app(PartialsParser::class)->execute(
                collect($dataContext->except)->filter($filter)->keys()->all()
            ),
        );
    }
}
