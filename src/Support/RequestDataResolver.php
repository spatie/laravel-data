<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;
use Spatie\LaravelData\Attributes\DataValidationAttribute;
use Spatie\LaravelData\Data;

class RequestDataResolver
{
    public static function create(ReflectionClass $class): self
    {
        return new self($class);
    }

    public function __construct(private ReflectionClass $class)
    {
    }

    public function get(Request $request): Data
    {
        /** @var \Spatie\LaravelData\Data $data */
        $data = collect($this->class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionProperty $property) => [
                $property->getName() => $request->input($property->getName()),
            ])
            ->pipe(fn(Collection $properties) => $this->class->newInstance(...$properties));

        return $data;
    }

    public function getRules(): array
    {
        return collect($this->class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionProperty $property) => [
                $property->getName() => $this->getRulesFromAttributes(...$property->getAttributes())
            ])
            ->toArray();
    }

    private function getRulesFromAttributes(ReflectionAttribute ...$attributes): array
    {
        $rules = [];

        foreach ($attributes as $attribute){
            $initiatedAttribute = $attribute->newInstance();

            if($initiatedAttribute instanceof DataValidationAttribute){
                $rules = array_merge($rules, $initiatedAttribute->getRules());
            }
        }

        return $rules;
    }
}
