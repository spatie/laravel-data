<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Illuminate\Database\Eloquent\Model;
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
}
