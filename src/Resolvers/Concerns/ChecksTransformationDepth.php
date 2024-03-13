<?php

namespace Spatie\LaravelData\Resolvers\Concerns;

use Spatie\LaravelData\Exceptions\MaxTransformationDepthReached;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

trait ChecksTransformationDepth
{
    public function hasReachedMaxTransformationDepth(TransformationContext $context): bool
    {
        return $context->maxDepth !== null && $context->depth >= $context->maxDepth;
    }

    public function handleMaxDepthReached(TransformationContext $context): array
    {
        if ($context->throwWhenMaxDepthReached) {
            throw MaxTransformationDepthReached::create($context->maxDepth);
        }

        return [];
    }
}
