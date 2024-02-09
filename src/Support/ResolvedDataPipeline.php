<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Support\Creation\CreationContext;

class ResolvedDataPipeline
{
    /**
     * @param array<\Spatie\LaravelData\Normalizers\Normalizer> $normalizers
     * @param array<\Spatie\LaravelData\DataPipes\DataPipe> $pipes
     */
    public function __construct(
        protected array $normalizers,
        protected array $pipes,
        protected DataClass $dataClass,
    ) {
    }

    public function execute(mixed $value, CreationContext $creationContext): array
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

        $properties = ($this->dataClass->name)::prepareForPipeline($properties);

        foreach ($this->pipes as $pipe) {
            $piped = $pipe->handle($value, $this->dataClass, $properties, $creationContext);

            $properties = $piped;
        }

        return $properties;
    }
}
