<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\Creation\CreationContext;

class ResolvedDataPipeline
{
    /**
     * @param array<Normalizer> $normalizers
     * @param array<DataPipe> $pipes
     */
    public function __construct(
        protected array $normalizers,
        protected array $pipes,
        protected DataClass $dataClass,
    ) {
    }

    public function execute(mixed $value, CreationContext $creationContext): array
    {
        $normalizedValue = $this->normalize($value);

        $normalizedValue = $this->transformNormalizedToArray(
            $normalizedValue,
            $creationContext,
        );

        return $this->runPipelineOnNormalizedValue($value, $normalizedValue, $creationContext);
    }

    public function normalize(mixed $value): array|Normalized
    {
        $properties = null;

        foreach ($this->normalizers as $normalizer) {
            $properties = $normalizer->normalize($value);

            if ($properties !== null) {
                break;
            }
        }

        if ($properties === null) {
            throw CannotCreateData::noNormalizerFound($this->dataClass->name, $value);
        }

        return $properties;
    }

    public function runPipelineOnNormalizedValue(
        mixed $value,
        array|Normalized $normalizedValue,
        CreationContext $creationContext
    ): array {
        $properties = ($this->dataClass->name)::prepareForPipeline(
            $this->transformNormalizedToArray($normalizedValue, $creationContext)
        );

        foreach ($this->pipes as $pipe) {
            $piped = $pipe->handle($value, $this->dataClass, $properties, $creationContext);

            $properties = $piped;
        }

        return $properties;
    }

    public function transformNormalizedToArray(
        Normalized|array $normalized,
        CreationContext $creationContext,
    ): array {
        if (! $normalized instanceof Normalized) {
            return $normalized;
        }

        $properties = [];

        $dataClassToNormalize = $creationContext->dataClass !== $this->dataClass->name
            ? app(DataConfig::class)->getDataClass($creationContext->dataClass)
            : $this->dataClass;

        foreach ($dataClassToNormalize->properties as $property) {
            $name = $property->inputMappedName ?? $property->name;

            $value = $normalized->getProperty($name, $property);

            if ($value === UnknownProperty::create()) {
                continue;
            }

            $properties[$name] = $value;
        }

        return $properties;
    }
}
