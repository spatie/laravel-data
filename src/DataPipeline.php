<?php

namespace Spatie\LaravelData;

use Illuminate\Support\Collection;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\DataConfig;

class DataPipeline
{
    protected array $normalizers = [];

    protected array $pipes = [];

    protected mixed $value;

    protected string $classString;

    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public static function create(): static
    {
        return app(static::class);
    }

    public function using(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function into(string $classString): static
    {
        $this->classString = $classString;

        return $this;
    }

    public function normalizer(string|Normalizer $normalizer): static
    {
        $this->normalizers[] = $normalizer;

        return $this;
    }

    public function through(string|DataPipe $pipe): static
    {
        $this->pipes[] = $pipe;

        return $this;
    }

    public function firstThrough(string|DataPipe $pipe): static
    {
        array_unshift($this->pipes, $pipe);

        return $this;
    }

    public function execute(): Collection
    {
        /** @var \Spatie\LaravelData\Normalizers\Normalizer[] $normalizers */
        $normalizers = array_map(
            fn (string|Normalizer $normalizer) => is_string($normalizer) ? app($normalizer) : $normalizer,
            $this->normalizers
        );

        /** @var \Spatie\LaravelData\DataPipes\DataPipe[] $pipes */
        $pipes = array_map(
            fn (string|DataPipe $pipe) => is_string($pipe) ? app($pipe) : $pipe,
            $this->pipes
        );

        $properties = null;

        foreach ($normalizers as $normalizer) {
            $properties = $normalizer->normalize($this->value);

            if ($properties !== null) {
                break;
            }
        }

        if ($properties === null) {
            throw CannotCreateData::noNormalizerFound($this->classString, $this->value);
        }

        $properties = collect($properties);

        $class = $this->dataConfig->getDataClass($this->classString);

        $properties = ($class->name)::prepareForPipeline($properties);

        foreach ($pipes as $pipe) {
            $piped = $pipe->handle($this->value, $class, $properties);

            $properties = $piped;
        }

        return $properties;
    }
}
