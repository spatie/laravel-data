<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelData\Data;

class DataFromRequestResolver
{
    public function __construct(
        protected Request $request,
        protected DataValidationRulesResolver $dataValidationRulesResolver
    ) {
    }

    public function get(string $class): Data
    {
        /** @var \Spatie\LaravelData\RequestData|string $class */
        $rules = $this->dataValidationRulesResolver
            ->execute($class)
            ->merge($class::rules())
            ->toArray();

        $validator = Validator::make(
            $this->request->all(),
            $rules,
            $class::messages(),
            $class::attributes()
        );

        $class::withValidator($validator);

        $validator->validate();

        return $class::create($this->request);
    }
}
