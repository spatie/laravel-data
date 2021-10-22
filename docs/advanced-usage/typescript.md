---
title: Transforming to TypeScript
weight: 2
---

Thanks to the [typescript-transformer](https://github.com/spatie/typescript-transformer) package, it is possible to automatically transform data objects into TypeScript definitions.

For example, the following data object:

```php
class DataObject extends Data{
    public function __construct(
        public null|int $nullable,
        public int $int,
        public bool $bool,
        public string $string,
        public float $float,
        /** @var string[] */
        public array $array,
        public Lazy|string $lazy,
        public SimpleData $simpleData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $dataCollection,
    )
    {
    }
}
```

Would be transformed to the following TypeScript type:

```tsx
{
    nullable: number | null;
    int: number;
    bool: boolean;
    string: string;
    float: number;
    array: Array<string>;
    lazy?: string;
    simpleData: SimpleData;
    dataCollection: Array<SimpleData>;
}
```

To enable this, add the `DataTypeScriptTransformer` transformer to the transformers in the `typescript-transformer.php` config file.

You then should annotate each data object with a `/** @typescript */` annotation or a `#[TypeScript]` attribute.

If you want to transform all the data objects within your application to TypeScript, you can use the `DataTypeScriptCollector`, which should be added to the collectors in `typescript-transformer.php`.
