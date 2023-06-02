<?php

namespace Spatie\LaravelData\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:data')]
class DataMakeCommand extends GeneratorCommand
{
    protected $name = 'make:data';

    protected $description = 'Create a new data class';

    protected $type = 'Data';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/data.stub');
    }

    protected function resolveStubPath($stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $namespace = trim($this->option('namespace') ?? 'Data', '\\');

        return trim($rootNamespace . '\\' . $namespace, '\\');
    }

    protected function qualifyClass($name): string
    {
        $suffix = trim($this->option('suffix') ?? 'Data');
        if (! empty($suffix) && ! Str::endsWith($name, $suffix)) {
            $name = $name . $suffix;
        }

        return parent::qualifyClass($name);
    }

    protected function getOptions(): array
    {
        return [
            [
                'namespace',
                'N',
                InputOption::VALUE_REQUIRED,
                'The namespace (under \App) to place this Data class.',
                config('data.commands.make.namespace', 'Data'),
            ],
            [
                'suffix',
                's',
                InputOption::VALUE_REQUIRED,
                'Suffix the class with this value.',
                config('data.commands.make.suffix', 'Data'),
            ],
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Create the Data class even if the file already exists.',
            ],
        ];
    }
}
