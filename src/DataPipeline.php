<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\ResolvedDataPipeline;

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

    public function resolve(): ResolvedDataPipeline
    {
        $normalizers = array_merge(
            $this->normalizers,
            $this->classString::normalizers()
        );

        /** @var \Spatie\LaravelData\Normalizers\Normalizer[] $normalizers */
        $normalizers = array_map(
            fn (string|Normalizer $normalizer) => is_string($normalizer) ? app($normalizer) : $normalizer,
            $normalizers
        );

        /** @var \Spatie\LaravelData\DataPipes\DataPipe[] $pipes */
        $pipes = array_map(
            fn (string|DataPipe $pipe) => is_string($pipe) ? app($pipe) : $pipe,
            $this->pipes
        );

        return new ResolvedDataPipeline(
            $normalizers,
            $pipes,
            $this->dataConfig->getDataClass($this->classString)
        );
    }
}
