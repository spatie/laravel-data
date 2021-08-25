<?php

namespace Spatie\LaravelData\Tests\Factories;

use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PromotedParameter;
use Nette\PhpGenerator\Property;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class DataMagicMethodFactory
{
    private string $body;

    private string $inputType;

    private string $inputName;

    public function __construct(private string $name)
    {
    }

    public static function new(string $name): static
    {
        return new self($name);
    }

    public function withInputType(string $inputType, string $inputName = 'payload'): self
    {
        $clone = clone $this;

        $clone->inputType = $inputType;
        $clone->inputName = $inputName;

        return $clone;
    }

    public function withBody(string $body): self
    {
        $clone = clone $this;

        $clone->body = $body;

        return $clone;
    }

    public function create(): Method
    {
        $method = new Method($this->name);

        $method->addBody($this->body);
        $method->setStatic(true);
        $method->setReturnType('static');
        $method->setParameters([(new Parameter($this->inputName))->setType($this->inputType)]);

        return $method;
    }
}
