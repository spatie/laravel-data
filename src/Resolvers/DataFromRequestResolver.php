<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;

class DataFromRequestResolver
{
    public function __construct(
        protected Request $request,
        protected DataConfig $dataConfig,
        protected DataValidationRulesResolver $dataValidationRulesResolver
    ) {
    }

    public function get(string $class): Data
    {
        if ($this->dataConfig->getDataClass($class)->hasAuthorizationMethod()) {
            $this->checkAuthorization($class);
        }

        /** @var \Spatie\LaravelData\Data|string $class */
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

        return $class::from($this->request);
    }

    private function checkAuthorization(string $class)
    {
        if ($class::authorized() === false) {
            throw new AuthorizationException();
        }
    }
}
