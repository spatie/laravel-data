<?php

namespace Spatie\LaravelData\Support;

use ReflectionClass;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Transformers\Transformer;

class DataConfig
{
    /** @var array<string, \Spatie\LaravelData\Support\DataClass> */
    private array $dataClasses = [];

    /** @var \Spatie\LaravelData\Transformers\Transformer[] */
    protected array $transformers = [];

    /** @var array<string, string> */
    protected array $casts = [];

    /** @var \Spatie\LaravelData\AutoRules\AutoRule[] */
    private array $autoRules = [];

    public function __construct(array $config)
    {
        $this->transformers = array_map(
            fn (string $transformerClass) => app($transformerClass),
            $config['transformers'] ?? []
        );

        $this->autoRules = array_map(
            fn (string $autoRuleClass) => app($autoRuleClass),
            $config['auto_rules'] ?? []
        );

        $this->casts = $config['casts'];
    }

    public function getDataClass(string $class): DataClass
    {
        if (array_key_exists($class, $this->dataClasses)) {
            return $this->dataClasses[$class];
        }

        return $this->dataClasses[$class] = DataClass::create(new ReflectionClass($class));
    }

    public function getCastForType(string $type): ?Cast
    {
        foreach ($this->casts as $castable => $cast) {
            if (ltrim($type, ' \\') === ltrim($castable, ' \\')) {
                return app($cast);
            }

            if (is_a($type, $castable, true)) {
                return app($cast);
            }
        }

        return null;
    }

    public function getAutoRules(): array
    {
        return $this->autoRules;
    }

    public function findTransformerForValue(mixed $value): ?Transformer
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }

        return null;
    }
}
