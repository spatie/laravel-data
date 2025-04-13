<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\LoadRelation;
use Spatie\LaravelData\Support\DataProperty;

class NormalizedModel implements Normalized
{
    protected array $properties = [];

    protected ReflectionProperty $attributesProperty;

    protected ReflectionProperty $castsProperty;

    public function __construct(
        protected Model $model,
    ) {
    }

    public function getProperty(string $name, DataProperty $dataProperty): mixed
    {
        $propertyName = $this->model::$snakeAttributes ? Str::snake($name) : $name;

        $value = array_key_exists($propertyName, $this->properties)
            ? $this->properties[$propertyName]
            : $this->fetchNewProperty($propertyName, $dataProperty);

        if ($value === null && ! $dataProperty->type->isNullable) {
            return UnknownProperty::create();
        }

        return $value;
    }

    protected function fetchNewProperty(string $name, DataProperty $dataProperty): mixed
    {
        $camelName = Str::camel($name);

        if ($dataProperty->attributes->has(LoadRelation::class)) {
            if (method_exists($this->model, $name)) {
                $this->model->loadMissing($name);
            } elseif (method_exists($this->model, $camelName)) {
                $this->model->loadMissing($camelName);
            }
        }

        if ($this->model->relationLoaded($name)) {
            return $this->properties[$name] = $this->model->getRelation($name);
        }
        if ($this->model->relationLoaded($camelName)) {
            return $this->properties[$name] = $this->model->getRelation($camelName);
        }

        if ($this->hasModelAttribute($name) || (! $this->model->isRelation($name) && ! $this->model->isRelation($camelName))) {
            try {
                return $this->properties[$name] = $this->model->getAttribute($name);
            } catch (MissingAttributeException) {
                // Fallback if missing Attribute exception is thrown
            }
        }

        return $this->properties[$name] = UnknownProperty::create();
    }


    protected function hasModelAttribute(string $name): bool
    {
        if (method_exists($this->model, 'hasAttribute')) {
            return $this->model->hasAttribute($name);
        }

        // TODO: to remove that when we stop supporting Laravel 10

        if (! isset($this->attributesProperty)) {
            $this->attributesProperty = new ReflectionProperty($this->model, 'attributes');
        }

        if (! isset($this->castsProperty)) {
            $this->castsProperty = new ReflectionProperty($this->model, 'casts');
        }

        return array_key_exists($name, $this->attributesProperty->getValue($this->model)) ||
            array_key_exists($name, $this->castsProperty->getValue($this->model)) ||
            $this->model->hasGetMutator($name) ||
            $this->model->hasAttributeMutator($name);
    }
}
