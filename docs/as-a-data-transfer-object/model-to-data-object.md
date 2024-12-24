---
title: From a model
weight: 10
---

It is possible to create a data object from a model, let's say we have the following model:

```php
class Artist extends Model
{

}
```

It has the following columns in the database:

- id
- first_name
- last_name
- created_at
- updated_at

We can create a data object from this model like this:

```php
class ArtistData extends Data
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

We now can create a data object from the model like this:

```php
$artist = ArtistData::from(Artist::find(1));
```

## Casts

A model can have casts, these casts will be called before a data object is created. Let's extend the model:

```php
use Illuminate\Database\Eloquent\Casts\AsEnumArrayObject;

class Artist extends Model
{
    public function casts(): array
    {
       return [
            'properties' => 'array',
            'specialties' => AsEnumArrayObject::of(Specialty::class),
       ];
    }
}
```

Within the database the `properties` column will be stored as a JSON string, the `specialties` column will be stored an array of enum values within, but in the data object we can just use the array type:

```php
class ArtistData extends Data
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public array $properties;
    public array $specialties;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

## Attributes & Accessors

Laravel allows you to define attributes on a model, these will be called before a data object is created. Let's extend
the model:

```php
class Artist extends Model
{
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

We now can use the attribute in the data object:

```php
class ArtistData extends Data
{
    public int $id;
    public string $full_name;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

Remember: we need to use the snake_case version of the attribute in the data object since that's how it is stored in the
model. Read on for a more elegant solution when you want to use camelCase property names in your data object.

It is also possible to define accessors on a model which are the successor of the attributes:

```php
class Artist extends Model
{
    public function getFullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->first_name} {$this->last_name}");
    }
}
```

With the same data object we created earlier we can now use the accessor.

## Mapping property names

Sometimes you want to use camelCase property names in your data object, but the model uses snake_case. You can use
an `MapInputName` to map the property names:

```php
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

class ArtistData extends Data
{
    public int $id;
    #[MapInputName(SnakeCaseMapper::class)]
    public string $fullName;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

An even more elegant solution would be to map every property within the data object:

```php
#[MapInputName(SnakeCaseMapper::class)]
class ArtistData extends Data
{
    public int $id;
    public string $fullName;
    public CarbonImmutable $createdAt;
    public CarbonImmutable $updatedAt;
}
```

## Relations

Let's create a new model:

```php
class Song extends Model
{
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}
```

Which has the following columns in the database:

- id
- artist_id
- title

We update our previous model as such:

```php
class Artist extends Model
{
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }
}
```

We can now create a data object like this:

```php
class SongData extends Data
{
    public int $id;
    public string $title;
}
```

And update our previous data object like this:

```php
class ArtistData extends Data
{
    public int $id;
    /** @var array<SongData>  */
    public array $songs;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

We can now create a data object with the relations like this:

```php
$artist = ArtistData::from(Artist::with('songs')->find(1));
```

When you're not loading the relations in advance, `null` will be returned for the relation.

It is however possible to load the relation on the fly by adding the `LoadRelation` attribute to the property:

```php
class ArtistData extends Data
{
    public int $id;
    /** @var array<SongData>  */
    #[LoadRelation]
    public array $songs;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

Now the data object with relations can be created like this:

```php
$artist = ArtistData::from(Artist::find(1));
```

We even eager-load the relation for performance, neat!

### Be careful with automatic loading of relations

Let's update the `SongData` class like this:

```php
class SongData extends Data
{
    public int $id;
    public string $title;
    #[LoadRelation]
    public ArtistData $artist;
}
```

When we now create a data object like this:

```php
$song = SongData::from(Song::find(1));
```

We'll end up in an infinite loop, since the `SongData` class will try to load the `ArtistData` class, which will try to
load the `SongData` class, and so on.
