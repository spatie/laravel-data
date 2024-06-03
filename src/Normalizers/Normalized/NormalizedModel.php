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

        return $this->properties[$propertyName] ?? $this->fetchNewProperty($propertyName, $dataProperty);
    }

    protected function initialize(Model $model): void
    {
        $this->properties = $model->toArray();

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

        foreach ($model->getRelations() as $key => $relation) {
            $key = $model::$snakeAttributes ? Str::snake($key) : $key;

            if (isset($this->properties[$key])) {
                $this->properties[$key] = $relation;
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

        if (! $dataProperty->attributes->contains(fn (object $attribute) => $attribute::class === LoadRelation::class)) {
            return UnknownProperty::create();
        }

        $studlyName = Str::studly($name);

        if (! method_exists($this->model, $studlyName)) {
            return UnknownProperty::create();
        }

        $this->model->load($studlyName);

        return $this->properties[$name] = $this->model->{$studlyName};
    }
}
