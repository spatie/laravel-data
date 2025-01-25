<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\LoadRelation;
use Spatie\LaravelData\Support\DataProperty;

class NormalizedModel implements Normalized
{
    protected array $properties = [];

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

        if ($dataProperty->attributes->hasAttribute(LoadRelation::class)) {
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

        if (! $this->model->isRelation($name) && ! $this->model->isRelation($camelName)) {
            try {
                return $this->properties[$name] = $this->model->getAttribute($name);
            } catch (MissingAttributeException) {
                // Fallback if missing Attribute
            }
        }

        return $this->properties[$name] = UnknownProperty::create();
    }
}
