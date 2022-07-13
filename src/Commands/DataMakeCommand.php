<?php

namespace Spatie\LaravelData\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class DataMakeCommand extends GeneratorCommand
{
    protected $name = 'make:data';
    protected $description = 'Create a new data class';
    protected $type = 'Data';

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/data.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../..'.$stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Data';
    }

    protected function qualifyClass($name)
    {
        if (! Str::endsWith($name, 'Data')) {
            $name = $name.'Data';
        }

        return parent::qualifyClass($name);
    }
}
