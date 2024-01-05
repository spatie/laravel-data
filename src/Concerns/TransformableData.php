<?php

namespace Spatie\LaravelData\Concerns;

use Exception;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\BaseDataCollectable as BaseDataCollectableContract;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait TransformableData
{
    public function transform(
        null|TransformationContextFactory|TransformationContext $transformationContext = null,
    ): array {
        if ($transformationContext === null) {
            $transformationContext = new TransformationContext();
        }

        if ($transformationContext instanceof TransformationContextFactory) {
            $transformationContext = $transformationContext->get($this);
        }

        $resolver = match (true) {
            $this instanceof BaseDataContract => DataContainer::get()->transformedDataResolver(),
            $this instanceof BaseDataCollectableContract => DataContainer::get()->transformedDataCollectionResolver(),
            default => throw new Exception('Cannot transform data object')
        };

        $dataContext = $this->getDataContext();

        /** @var TransformationContext $transformationContext */

        if ($dataContext->includePartials->count() > 0) {
            $transformationContext->includedPartials->addAll(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($this, $dataContext->includePartials)
            );
        }

        if ($dataContext->excludePartials->count() > 0) {
            $transformationContext->excludedPartials->addAll(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($this, $dataContext->excludePartials)
            );
        }

        if ($dataContext->onlyPartials->count() > 0) {
            $transformationContext->onlyPartials->addAll(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($this, $dataContext->onlyPartials)
            );
        }

        if ($dataContext->exceptPartials->count() > 0) {
            $transformationContext->exceptPartials->addAll(
                $dataContext->getResolvedPartialsAndRemoveTemporaryOnes($this, $dataContext->exceptPartials)
            );
        }

        return $resolver->execute($this, $transformationContext);
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
