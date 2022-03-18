<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\TransformPipes\TransformPipe;

class TransformPipeline
{
    private array $pipes = [];

    private Data $data;

    public function __construct(private DataConfig $dataConfig)
    {
    }

    public static function create(): static
    {
        return app(static::class);
    }

    public function using(Data $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function through(string|TransformPipe $pipe): static
    {
        $this->pipes[] = $pipe;

        return $this;
    }

    public function execute(): array
    {
        /** @var \Spatie\LaravelData\TransformPipes\TransformPipe[] $pipes */
        $pipes = array_map(
            fn (string|DataPipe $pipe) => is_string($pipe) ? app($pipe) : $pipe,
            $this->pipes
        );

        $class = $this->dataConfig->getDataClass($this->data::class);

        $properties = collect();

        foreach ($pipes as $pipe) {
            $piped = $pipe->handle($this->data, $class, $properties);

            $properties = $piped;
        }

        return $properties->all();
    }
}
