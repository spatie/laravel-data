<?php

namespace Spatie\LaravelData\Support;

use ReflectionClass;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Transformers\Transformer;

class DataConfig
{
    /** @var array<string, \Spatie\LaravelData\Support\DataClass> */
    protected array $dataClasses = [];

    /** @var array<string, \Spatie\LaravelData\Transformers\Transformer> */
    protected array $transformers = [];

    /** @var array<string, \Spatie\LaravelData\Casts\Cast> */
    protected array $casts = [];

    /** @var \Spatie\LaravelData\RuleInferrers\RuleInferrer[] */
    protected array $ruleInferrers;

    public function __construct(array $config)
    {
        $this->ruleInferrers = array_map(
            fn (string $ruleInferrerClass) => app($ruleInferrerClass),
            $config['rule_inferrers'] ?? []
        );

        foreach ($config['transformers'] ?? [] as $transformable => $transformer) {
            $this->transformers[ltrim($transformable, ' \\')] = app($transformer);
        }

        foreach ($config['casts'] ?? [] as $castable => $cast) {
            $this->casts[ltrim($castable, ' \\')] = app($cast);
        }
    }

    public function getDataClass(string $class): DataClass
    {
        if (array_key_exists($class, $this->dataClasses)) {
            return $this->dataClasses[$class];
        }

        return $this->dataClasses[$class] = DataClass::create(new ReflectionClass($class));
    }

    public function findGlobalCastForProperty(DataProperty $property): ?Cast
    {
        if ($property->isBuiltIn()) {
            return null;
        }

        foreach ($property->types()->all() as $type) {
            if ($cast = $this->findSuitableReplacerForClass($this->casts, $type)) {
                return $cast;
            }
        }

        return null;
    }

    public function findGlobalTransformerForValue(mixed $value): ?Transformer
    {
        if (gettype($value) !== 'object') {
            return null;
        }

        return $this->findSuitableReplacerForClass($this->transformers, get_class($value));
    }

    public function getRuleInferrers(): array
    {
        return $this->ruleInferrers;
    }

    protected function findSuitableReplacerForClass(
        array $replacers,
        string $class
    ) {
        foreach ($replacers as $replaceable => $replacer) {
            if ($class === $replaceable) {
                return $replacer;
            }

            if (is_a($class, $replaceable, true)) {
                return $replacer;
            }
        }

        return null;
    }
}
