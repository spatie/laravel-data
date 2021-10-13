<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
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
        /** @var \Spatie\LaravelData\Data|string $class */
        if ($this->dataConfig->getDataClass($class)->hasAuthorizationMethod()) {
            $this->checkAuthorization($class);
        }

        $class::validate($this->request);

        return $class::from($this->request);
    }

    private function checkAuthorization(string $class)
    {
        /** @psalm-suppress UndefinedMethod */
        if ($class::authorized() === false) {
            throw new AuthorizationException();
        }
    }
}
