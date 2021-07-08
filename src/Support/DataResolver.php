<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Http\Request;
use ReflectionClass;
use Spatie\LaravelData\Data;
use Validator;

class DataResolver
{
    public function __construct(private Request $request)
    {
    }

    public function get(string $class): Data
    {
        /** @var \Spatie\LaravelData\RequestData|string $class */
        $resolver = RequestDataResolver::create(new ReflectionClass($class));

        $rules = array_merge(
            $resolver->getRules(),
            $class::getRules()
        );

        $validator = Validator::make($this->request->all(), $rules);

        $validator->validate();

        $data = $class::createFromRequest($this->request);

        if ($data === null) {
            $data = $resolver->get($this->request);
        }

        return $data;
    }
}
