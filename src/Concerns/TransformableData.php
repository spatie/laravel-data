<?php

namespace Spatie\LaravelData\Concerns;

use Exception;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\BaseDataCollectable as BaseDataCollectableContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait TransformableData
{
    public function transform(
        null|TransformationContextFactory|TransformationContext $transformationContext = null,
    ): array {
        $transformationContext = match (true) {
            $transformationContext instanceof TransformationContext => $transformationContext,
            $transformationContext instanceof TransformationContextFactory => $transformationContext->get($this),
            $transformationContext === null => new TransformationContext()
        };

        $resolver = match (true) {
            $this instanceof BaseDataContract => DataContainer::get()->transformedDataResolver(),
            $this instanceof BaseDataCollectableContract => DataContainer::get()->transformedDataCollectableResolver(),
            default => throw new Exception('Cannot transform data object')
        };

        if ($this instanceof IncludeableDataContract) {
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
        return json_encode($this->transform(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->transform();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class, $arguments);
    }
}
