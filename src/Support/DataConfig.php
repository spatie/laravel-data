<?php

namespace Spatie\LaravelData\Support;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Transformers\Transformer;

class DataConfig
{
    /** @var array<string, array<\Spatie\LaravelData\Support\DataProperty>> */
    private array $properties = [];

    /** @var \Spatie\LaravelData\Transformers\Transformer[] */
    protected array $transformers = [];

    /** @var array<string, \Spatie\LaravelData\Casts\Cast> */
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

        foreach ($config['casts'] ?? [] as $type => $castClass) {
            $type = ltrim($type, ' \\');

            $this->casts[$type] = app($castClass);
        }
    }

    public function getDataProperties(string $class): array
    {
        if (array_key_exists($class, $this->properties)) {
            return $this->properties[$class];
        }

        $properties = (new ReflectionClass($class))->getProperties(ReflectionProperty::IS_PUBLIC);

        return $this->properties[$class] = array_map(
            fn (ReflectionProperty $property) => DataProperty::create($property),
            $properties
        );
    }

    public function getCastForType(string $type): ?Cast
    {
        return $this->casts[ltrim($type, ' \\')] ?? null;
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
