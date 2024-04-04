<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Lazy;

class RelationalLazy extends Lazy
{
    protected function __construct(
        protected string $relation,
        protected Model $model,
        protected Closure $value,
    ) {
    }

    public function resolve(): mixed
    {
        return $this->model->{$this->relation} !== null ? ($this->value)() : null;
    }

    public function shouldBeIncluded(): bool
    {
        return $this->model->relationLoaded($this->relation);
    }

    public function __serialize(): array
    {
        return [
            'relation' => $this->relation,
            'model' => $this->model,
            'value' => new SerializableClosure($this->value),
            'defaultIncluded' => $this->defaultIncluded,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->relation = $data['relation'];
        $this->model = $data['model'];
        $this->value = $data['value']->getClosure();
        $this->defaultIncluded = $data['defaultIncluded'];
    }
}
