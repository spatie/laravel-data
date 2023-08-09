<?php

namespace Spatie\LaravelData\Support;

use ReflectionClass;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Transformers\Transformer;

class DataConfig
{
    /** @var array<string, \Spatie\LaravelData\Support\DataClass> */
    protected array $dataClasses = [];

    /** @var array<string, \Spatie\LaravelData\Transformers\Transformer> */
    protected array $transformers = [];

    /** @var array<string, \Spatie\LaravelData\Casts\Cast> */
    protected array $casts = [];

    /** @var array<string, \Spatie\LaravelData\Support\ResolvedDataPipeline> */
    protected array $resolvedDataPipelines = [];

    /** @var \Spatie\LaravelData\RuleInferrers\RuleInferrer[] */
    protected array $ruleInferrers;

    public readonly DataClassMorphMap $morphMap;

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

        $this->morphMap = new DataClassMorphMap();
    }

    public function getDataClass(string $class): DataClass
    {
        if (array_key_exists($class, $this->dataClasses)) {
            return $this->dataClasses[$class];
        }

        return $this->dataClasses[$class] = DataClass::create(new ReflectionClass($class));
    }

    public function getResolvedDataPipeline(string $class): ResolvedDataPipeline
    {
        if (array_key_exists($class, $this->resolvedDataPipelines)) {
            return $this->resolvedDataPipelines[$class];
        }

        return $this->resolvedDataPipelines[$class] = $class::pipeline()->resolve();
    }

    public function findGlobalCastForProperty(DataProperty $property): ?Cast
    {
        foreach ($property->type->acceptedTypes as $acceptedType => $baseTypes) {
            foreach ([$acceptedType, ...$baseTypes] as $type) {
                if ($cast = $this->casts[$type] ?? null) {
                    return $cast;
                }
            }
        }

        return null;
    }

    public function findGlobalTransformerForValue(mixed $value): ?Transformer
    {
        if (gettype($value) !== 'object') {
            return null;
        }

        foreach ($this->transformers as $transformable => $transformer) {
            if ($value::class === $transformable) {
                return $transformer;
            }

            if (is_a($value::class, $transformable, true)) {
                return $transformer;
            }
        }

        return null;
    }

    public function getRuleInferrers(): array
    {
        return $this->ruleInferrers;
    }

    /**
     * @param array<string, class-string<BaseData>> $map
     */
    public function enforceMorphMap(array $map): void
    {
        $this->morphMap->merge($map);
    }

    public function reset(): self
    {
        $this->dataClasses = [];
        $this->resolvedDataPipelines = [];

        return $this;
    }
}
