<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\LoadRelation;
use Spatie\LaravelData\Support\DataProperty;

class NormalizedModel implements Normalized
{
    protected array $properties = [];

    public function __construct(
        protected Model $model
    ) {
        $this->initialize($this->model);
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

    protected function initialize(Model $model): void
    {
        $this->properties = $model->withoutRelations()->toArray();

        foreach ($model->getDates() as $key) {
            if (isset($this->properties[$key])) {
                $this->properties[$key] = $model->getAttribute($key);
            }
        }

        foreach ($model->getCasts() as $key => $cast) {
            if ($this->isDateCast($cast)) {
                if (isset($this->properties[$key])) {
                    $this->properties[$key] = $model->getAttribute($key);
                }
            }
        }
    }

    protected function isDateCast(string $cast): bool
    {
        return in_array($cast, [
            'date',
            'datetime',
            'immutable_date',
            'immutable_datetime',
            'custom_datetime',
            'immutable_custom_datetime',
        ]);
    }

    protected function fetchNewProperty(string $name, DataProperty $dataProperty): mixed
    {
        if (in_array($name, $this->model->getMutatedAttributes())) {
            return $this->properties[$name] = $this->model->getAttribute($name);
        }

        $camelName = Str::camel($name);

        if ($dataProperty->attributes->contains(fn (object $attribute) => $attribute::class === LoadRelation::class)) {
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

        return $this->properties[$name] = UnknownProperty::create();
    }
}
