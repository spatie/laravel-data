<?php

namespace Spatie\LaravelData\Support\VarDumper;

use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

class VarDumperManager
{
    public function initialize(): void
    {
        $reflectionProperty = new ReflectionProperty(VarDumper::class, 'handler');
        $reflectionProperty->setAccessible(true);
        $handler = $reflectionProperty->getValue();

        if (! $handler) {
            $reflectionMethod = new ReflectionMethod(VarDumper::class, 'register');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke(null);
        }

        $handler = $reflectionProperty->getValue();

        /** @var VarCloner $cloner */
        $cloner = (new \ReflectionFunction($handler))->getClosureUsedVariables()['cloner'];

        $cloner->addCasters([
            DataObject::class => [DataVarDumperCaster::class, 'castDataObject'],
            DataCollectable::class => [DataVarDumperCaster::class, 'castDataCollectable']
        ]);
    }
}
