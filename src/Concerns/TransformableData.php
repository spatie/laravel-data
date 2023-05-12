<?php

namespace Spatie\LaravelData\Concerns;

use Exception;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\BaseDataCollectable as BaseDataCollectableContract;
use Spatie\LaravelData\Resolvers\TransformedDataCollectionResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Transformation\PartialTransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait TransformableData
{
    public function transform(
        null|TransformationContextFactory|TransformationContext $context = null,
    ): array {
        if ($context === null) {
            $context = new TransformationContext();
        }

        if ($context instanceof TransformationContextFactory) {
            $context = $context->get($this);
        }

        $resolver = match (true) {
            $this instanceof BaseDataContract => app(TransformedDataResolver::class),
            $this instanceof BaseDataCollectableContract => app(TransformedDataCollectionResolver::class),
            default => throw new Exception('Cannot transform data object')
        };

        $localPartials = PartialTransformationContext::create(
            $this,
            $this->getDataContext()->partialsDefinition
        );

        return $resolver->execute(
            $this,
            $context->mergePartials($localPartials)
        );
    }

    public function all(): array
    {
        return $this->transform(TransformationContextFactory::create()->transformValues(false));
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
