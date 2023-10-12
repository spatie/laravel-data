<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Arr;
use ReflectionClass;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Contracts\BaseData;

class DataConfig
{
    /** @var array<string, \Spatie\LaravelData\Support\DataClass> */
    protected array $dataClasses = [];

    /** @var array<string, \Spatie\LaravelData\Transformers\Transformer[]> */
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

        $this->setTransformers($config['transformers'] ?? []);

        foreach ($config['casts'] ?? [] as $castable => $cast) {
            $this->casts[ltrim($castable, ' \\')] = app($cast);
        }

        $this->morphMap = new DataClassMorphMap();
    }

    public function setTransformers(array $transformers): self
    {
        foreach ($transformers as $transformable => $transformers) {
            $this->transformers[ltrim($transformable, ' \\')] = array_map(resolve(...), Arr::wrap($transformers));
        }

        return $this;
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

    public function findGlobalTransformersForValue(mixed $value): array
    {
        if (gettype($value) !== 'object') {
            return [];
        }

        foreach ($this->transformers as $transformable => $transformers) {
            if ($value::class === $transformable) {
                return $transformers;
            }

            if (is_a($value::class, $transformable, true)) {
                return $transformers;
            }
        }

        return [];
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
