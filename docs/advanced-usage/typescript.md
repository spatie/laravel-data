---
title: Transforming to TypeScript
weight: 2
---

Thanks to the [typescript-transformer](https://spatie.be/docs/typescript-transformer) package, it is possible to
automatically transform data objects into TypeScript definitions.

For example, the following data object:

```php
class DataObject extends Data
{
    public function __construct(
        public null|int $nullable,
        public int $int,
        public bool $bool,
        public string $string,
        public float $float,
        /** @var string[] */
        public array $array,
        public Lazy|string $lazy,
        public Optional|string $optional,
        public SimpleData $simpleData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $dataCollection,
    )
    {
    }
}
```

... can be transformed to the following TypeScript type:

```tsx
{
    nullable: number | null;
    int: number;
    bool: boolean;
    string: string;
    float: number;
    array: Array<string>;
    lazy? : string;
    optional? : string;
    simpleData: SimpleData;
    dataCollection: Array<SimpleData>;
}
```

## Installation of extra package

First, you must install the spatie/laravel-typescript-transformer into your project.

```bash
composer require spatie/laravel-typescript-transformer
```

Next, publish the config file of the typescript-transformer package with:

```bash
php artisan vendor:publish --tag=typescript-transformer-config
```

Finally, add the `Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer` transformer to the
transformers in the `typescript-transformer.php` config file. 

If you're using the `DtoTransformer` provided by the package, then be sure to put the `DataTypeScriptTransformer` before the `DtoTransformer`.

## Usage

Annotate each data object that you want to transform to Typescript with a `/** @typescript */` annotation or
a `#[TypeScript]` attribute.

To [generate the typescript file](https://spatie.be/docs/typescript-transformer/v4/laravel/executing-the-transform-command)
, run this command:

```php
php artisan typescript:transform
```

If you want to transform all the data objects within your application to TypeScript, you can use
the `DataTypeScriptCollector`, which should be added to the collectors in `typescript-transformer.php`.

If you're using the `DefaultCollector` provided by the package, then be sure to put the `DataTypeScriptCollector` before the `DefaultCollector`.

### Optional types

An optional or lazy property will automatically be transformed into an optional type within TypeScript:

```php
class DataObject extends Data
{
    public function __construct(
        public Lazy|string $lazy,
        public Optional|string $optional,
    )
    {
    }
}
```

This will be transformed into:

```tsx
{
    lazy? : string;
    optional? : string;
}
```

If you want to have optional typed properties in TypeScript without typing your properties optional or lazy within PHP,
then you can use the `Optional` attribute from the `typescript-transformer` package.

Don't forget to alias it as `TypeScriptOptional` when you're already using this package's `Optional` type!

```php
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

class DataObject extends Data
{
    public function __construct(
        #[TypeScriptOptional]
        public int $id,
        public string $someString,
        public Optional|string $optional,
    )
    {
    }
}
```

### Dotted Notation Expansion

By default, dotted notation in property names is kept as-is in TypeScript. You can expand it into nested interfaces using the same **four approaches** as in data transformation:

```php
class UserData extends Data
{
    public function __construct(
        // 1. MapDotExpandedOutputName - output only
        #[MapDotExpandedOutputName('user.profile.name')]
        public string $name,
        
        // 2. MapDotExpandedName - input & output
        #[MapDotExpandedName('user.profile.email')]
        public string $email,
        
        // 3. MapOutputName with parameter - output only
        #[MapOutputName('user.settings.theme', expandDotNotation: true)]
        public string $theme,
        
        // 4. MapName with parameter - input & output
        #[MapName('user.settings.language', expandDotNotation: true)]
        public string $language,
    ) {
    }
}
```

This generates nested TypeScript interfaces:

```tsx
{
    user: {
        profile: {
            name: string;
            email: string;
        };
        settings: {
            theme: string;
            language: string;
        };
    };
}
```
