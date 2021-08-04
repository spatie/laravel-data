<?php

namespace Spatie\LaravelData\Tests\Factories;

use Nette\PhpGenerator\PromotedParameter;
use Nette\PhpGenerator\Property;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class DataPropertyBlueprintFactory
{
    private array $types = [];

    private bool $nullable = false;

    private bool $lazy = false;

    private bool $constructorPromoted = true;

    private ?string $annotationType = null;

    public function __construct(private string $name)
    {
    }

    public static function new(string $name): static
    {
        return new self($name);
    }

    public static function dataCollection(string $name, string $dataClass): static
    {
        return static::new($name)
            ->withType(DataCollection::class)
            ->withAnnotationType('\\'.$dataClass.'[]');
    }

    public function withType(string ...$types): self
    {
        $clone = clone $this;

        $clone->types = array_merge($this->types, $types);

        return $clone;
    }

    public function nullable(bool $nullable = true): self
    {
        $clone = clone $this;

        $clone->nullable = $nullable;

        return $clone;
    }

    public function lazy(bool $lazy = true): self
    {
        $clone = clone $this;

        $clone->lazy = $lazy;

        return $clone;
    }

    public function withAnnotationType(string $annotationType): self
    {
        $clone = clone $this;

        $clone->annotationType = $annotationType;

        return $clone;
    }

    public function withoutConstructorPromotion(bool $constructorPromoted = false): self
    {
        $clone = clone $this;

        $clone->constructorPromoted = $constructorPromoted;

        return $clone;
    }

    public function withTransformer(
        string $transformerClass,
    )
    {

    }

    public function create(): Property|PromotedParameter
    {
        $property = $this->constructorPromoted
            ? new PromotedParameter($this->name)
            : new Property($this->name);

        $property
            ->setType($this->resolveTypes())
            ->setNullable($this->nullable);

        if($this->annotationType){
            $property->setComment("@var {$this->annotationType} \${$this->name}");
        }

        return $property;
    }

    private function resolveTypes(): ?string
    {
        if(empty($this->types)){
            return null;
        }

        $types = $this->types;

        if($this->lazy){
            $types[] = Lazy::class;
        }

        if(count($types) === 1){
            return current($types);
        }

        return implode('|',$types);
    }
}
