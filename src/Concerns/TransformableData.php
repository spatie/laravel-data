<?php

namespace Spatie\LaravelData\Concerns;

use Exception;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\BaseDataCollectable as BaseDataCollectableContract;
use Spatie\LaravelData\Contracts\ContextableData as ContextableDataContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait TransformableData
{
    protected null|TransformationContextFactory|TransformationContext $_jsonSerializeContextOnce = null;

    public function transform(
        null|TransformationContextFactory|TransformationContext $transformationContext = null,
    ): array {
        $transformationContext = match (true) {
            $transformationContext instanceof TransformationContext => $transformationContext,
            $transformationContext instanceof TransformationContextFactory => $transformationContext->get($this),
            $transformationContext === null => new TransformationContext(
                maxDepth: config('data.max_transformation_depth'),
                throwWhenMaxDepthReached: config('data.throw_when_max_transformation_depth_reached')
            )
        };

        $resolver = match (true) {
            $this instanceof BaseDataContract => DataContainer::get()->transformedDataResolver(),
            $this instanceof BaseDataCollectableContract => DataContainer::get()->transformedDataCollectableResolver(),
            default => throw new Exception('Cannot transform data object')
        };

        if ($this instanceof IncludeableDataContract && $this instanceof ContextableDataContract) {
            $transformationContext->mergePartialsFromDataContext($this);
        }

        return $resolver->execute($this, $transformationContext);
    }

    public function all(): array
    {
        return $this->transform(TransformationContextFactory::create()->withValueTransformation(false));
    }

    public function toArray(): array
    {
        return $this->transform();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize(): array
    {
        $transformationContext = $this->_jsonSerializeContextOnce;

        if ($this->_jsonSerializeContextOnce) {
            $this->_jsonSerializeContextOnce = null;
        }

        return $this->transform($transformationContext);
    }

    public function jsonSerializeWithTransformationContext(null|TransformationContextFactory|TransformationContext $transformationContext): array
    {
        $this->_jsonSerializeContextOnce = $transformationContext;

        return $this->jsonSerialize();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class, $arguments);
    }
}
