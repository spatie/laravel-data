<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;
use Spatie\TypeScriptTransformer\Laravel\Transformers\DataClassTransformer;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\Types\StructType;

class DataTypeScriptTransformer extends DataClassTransformer
{
     // TODO implement this ourselves
}
