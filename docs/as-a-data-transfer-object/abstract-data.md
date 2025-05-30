---
title: Abstract Data
weight: 4
---

It is possible to create an abstract data class with subclasses extending it:

```php
abstract class Person extends Data
{
    public string $name;
}

class Singer extends Person
{
   public function __construct(
        public string $voice,
   ) {}
}

class Musician extends Person
{
   public function __construct(
        public string $instrument,
   ) {}
}
```

It is perfectly possible now to create individual instances as follows:

```php
Singer::from(['name' => 'Rick Astley', 'voice' => 'tenor']);
Musician::from(['name' => 'Rick Astley', 'instrument' => 'guitar']);
```

But what if you want to use this abstract type in another data class like this:

```php
class Contract extends Data
{
    public string $label;
    public Person $artist;
}
```

While the following may both be valid:

```php
Contract::from(['label' => 'PIAS', 'artist' => ['name' => 'Rick Astley', 'voice' => 'tenor']]);
Contract::from(['label' => 'PIAS', 'artist' => ['name' => 'Rick Astley', 'instrument' => 'guitar']]);
```

The package can't decide which subclass to construct for the property.

You can implement the `PropertyMorphableData` interface on the abstract class to solve this. This interface adds a `morph` method that will be used to determine which subclass to use. The `morph` method receives an array of properties limited to properties tagged by a  `PropertyForMorph` attribute.

```php
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;

abstract class Person extends Data implements PropertyMorphableData
{
    #[PropertyForMorph]
    public string $type;

    public string $name;
    
    public static function morph(array $properties): ?string
    {
        return match ($properties['type']) {
            'singer' => Singer::class,
            'musician' => Musician::class,
            default => null
        };
    }
}
```

The example above will work by adding this code, and the correct Data class will be constructed.

Since the morph functionality needs to run early within the data construction process, it bypasses the normal flow of constructing data objects so there are a few limitations:

- it is only allowed to use properties typed as string, int, or BackedEnum(int or string)
- When a property is typed as an enum, the value passed to the morph method will be an enum
- it can be that the value of a property within the morph method is null or a different type than expected since it runs before validation
- properties with mapped property names are still supported

It is also possible to use abstract data classes as collections as such:

```php
class Band extends Data
{
    public string $name;
    
    /**  @var array<Person> */
    public array $members;
}
```
