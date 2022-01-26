<?php

namespace Spatie\LaravelData\Console\Commands;

use Cerbero\EloquentInspector\Dtos\Property;
use Cerbero\EloquentInspector\Dtos\Relationship;
use Cerbero\EloquentInspector\Inspector;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Console\DataQualifier;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeDataCommand extends Command
{
    protected $signature = 'make:data
                            {model : The model to generate the data class for}
                            {--r|recursive : Whether to recursively generate data classes for all related models}
                            {--f|force : Whether to create the data class even if it already exists}';

    protected $description = 'Create a new model data class';

    protected $model;

    protected $dataClass;

    protected $useStatements = [];

    protected $relatedModels = [];

    protected $generatedData = [];

    public function handle(Filesystem $files)
    {
        $this->dataClass = $this->qualifyDataClass($this->getModel());

        $this->useClass(Data::class);

        $path = $this->getPath($this->dataClass);

        if ($files->exists($path) && !$this->option('force')) {
            $this->error("{$this->dataClass} already exists!");
            return false;
        }

        if (!$files->isDirectory(dirname($path))) {
            $files->makeDirectory(dirname($path), 0777, true, true);
        }

        $content = $files->get($this->getStub());

        $files->put($path, $this->replaceStubContent($content));

        $this->info($this->dataClass . ' created successfully.');

        $this->generatedData[$this->dataClass] = true;

        if ($this->option('recursive') && $this->model = $this->getNextRelatedModel()) {
            $this->handle($files);
        }
    }

    protected function qualifyDataClass(string $name): string
    {
        return $this->laravel[DataQualifier::class]->qualify($name);
    }

    protected function getModel(): string
    {
        if ($this->model) {
            return $this->model;
        }

        $model = $this->qualifyModel(trim($this->argument('model')));

        return $this->model = is_subclass_of($model, Model::class)
            ? $model
            : throw new InvalidArgumentException("Invalid model [$model]");
    }

    protected function qualifyModel(string $model)
    {
        $namespace = $this->laravel->getNamespace();
        $model = (string) Str::of($model)->trim()->ltrim('\\/')->replace('/', '\\');

        if (Str::startsWith($model, $namespace)) {
            return $model;
        }

        return is_dir(app_path('Models')) ? $namespace . 'Models\\' . $model : $namespace . $model;
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    protected function getStub()
    {
        $customPath = $this->laravel->basePath('stubs/model_data_class.stub');

        return file_exists($customPath) ? $customPath : __DIR__ . '/../stubs/model_data_class.stub';
    }

    protected function replaceStubContent(string $content): string
    {
        $replacements = [
            '{{ class }}' => class_basename($this->dataClass),
            '{{ namespace }}' => Str::beforeLast($this->dataClass, '\\'),
            '{{ model }}' => class_basename($this->getModel()),
            '{{ properties }}' => $this->getProperties(),
            '{{ relationships }}' => $this->getRelationships(),
            '{{ useStatements }}' => $this->getUseStatements(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    protected function getProperties(): string
    {
        $properties = Inspector::inspect($this->getModel())->getProperties();

        return implode(PHP_EOL, array_map(function (Property $property) {
            $name = Str::camel($property->name);
            $type = class_basename($property->type);

            if (class_exists($property->type)) {
                $this->useClass($property->type);
            }

            return "        public {$type} \${$name},";
        }, $properties));
    }

    protected function useClass(string ...$classes): static
    {
        $namespace = Str::beforeLast($this->dataClass, '\\');

        foreach ($classes as $class) {
            if (Str::beforeLast($class, '\\') != $namespace) {
                $this->useStatements[$this->dataClass][$class] = true;
            }
        }

        return $this;
    }

    protected function getRelationships(): string
    {
        $relationships = Inspector::inspect($this->getModel())->getRelationships();

        if (empty($relationships)) {
            return '';
        }

        return array_reduce($relationships, function ($carry, Relationship $relationship) {
            $carry .= "\n";
            $name = Str::camel($relationship->name);
            $relatedData = $this->qualifyDataClass($relationship->model);
            $relatedDataName = class_basename($relatedData);

            $this->relatedModels[] = $relationship->model;

            $this->useClass($relatedData);

            if (!$relationship->relatesToMany) {
                return $carry .= "        public {$relatedDataName} \${$name},";
            }

            $this->useClass(DataCollection::class, DataCollectionOf::class);

            return $carry .=
                "        #[DataCollectionOf({$relatedDataName}::class)]\n" .
                "        public DataCollection \${$name},";
        });
    }

    protected function getUseStatements(): string
    {
        return collect($this->useStatements[$this->dataClass])
            ->keys()
            ->sort()
            ->map(fn ($useStatement) => "use {$useStatement};")
            ->implode(PHP_EOL);
    }

    protected function getNextRelatedModel(): ?string
    {
        foreach ($this->relatedModels as $model) {
            if ($this->shouldGenerateDataForModel($model)) {
                return $model;
            }
        }

        return null;
    }

    protected function shouldGenerateDataForModel(string $model): bool
    {
        $data = $this->qualifyDataClass($model);
        $canCreateData = !file_exists($this->getPath($data)) || $this->option('force');
        $hasBeenGenerated = isset($this->generatedData[$data]);

        return $canCreateData && !$hasBeenGenerated;
    }
}
