<?php

namespace Spatie\LaravelData\Tests\Factories;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PromotedParameter;
use Nette\PhpGenerator\Property;
use Spatie\LaravelData\Data;

class DataBlueprintFactory
{
    private string $name;

    /** @var \Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory[] */
    private array $properties = [];

    public function __construct(?string $name = null)
    {
        $this->name = $name ?? 'Data' . uniqid();
    }

    public static function new(?string $name = null)
    {
        return new self($name);
    }

    public function withProperty(DataPropertyBlueprintFactory ...$properties): self
    {
        $clone = clone $this;

        $clone->properties = array_merge($this->properties, $properties);

        return $clone;
    }

    public function create(): string
    {
        eval($this->toString());

        return $this->name;
    }

    public function toString(): string
    {
        $class = new ClassType($this->name);

        $class->setExtends(Data::class);

        /** @var \Illuminate\Support\Collection $properties */
        /** @var \Illuminate\Support\Collection $promotedProperties */
        [$promotedProperties, $properties] = collect($this->properties)
            ->map(fn (DataPropertyBlueprintFactory $factory) => $factory->create())
            ->partition(fn (PromotedParameter | Property $property) => $property instanceof PromotedParameter);

        $class->setProperties($properties->all());

        if ($promotedProperties->isNotEmpty()) {
            $class->addMethod('__construct')->setParameters($promotedProperties->all());
        }

        return (string) $class;
    }
}
