---
title: Including and excluding properties
weight: 6
---

Sometimes you don't want all the properties included when transforming a data object to an array, for example:

```php
class AlbumData extends Data
{
    /**
    * @param Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public Collection $songs,
    ) {
    }
}
```

This will always output a collection of songs, which can become quite large. With lazy properties, we can include
properties when we want to:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            Lazy::create(fn() => SongData::collect($album->songs))
        );
    }
}
```

The `songs` key won't be included in the resource when transforming it from a model. Because the closure that provides
the data won't be called when transforming the data object unless we explicitly demand it.

Now when we transform the data object as such:

```php
AlbumData::from(Album::first())->toArray();
```

We get the following array:

```php
[
    'title' => 'Together Forever',
]
```

As you can see, the `songs` property is missing in the array output. Here's how you can include it.

```php
AlbumData::from(Album::first())->include('songs');
```

## Including lazy properties

Lazy properties will only be included when the `include` method is called on the data object with the property's name.

It is also possible to nest these includes. For example, let's update the `SongData` class and make all of its
properties lazy:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            Lazy::create(fn() => $song->title),
            Lazy::create(fn() => $song->artist)
        );
    }
}
```

Now `name` or `artist` should be explicitly included. This can be done as such on the `AlbumData`:

```php
AlbumData::from(Album::first())->include('songs.name', 'songs.artist');
```

Or you could combine these includes:

```php
AlbumData::from(Album::first())->include('songs.{name, artist}');
```

If you want to include all the properties of a data object, you can do the following:

```php
AlbumData::from(Album::first())->include('songs.*');
```

Explicitly including properties of data objects also works on a single data object. For example, our `UserData` looks
like this:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            Lazy::create(fn() => SongData::from($user->favorite_song))
        );
    }
}
```

We can include properties of the data object just like we would with collections of data objects:

```php
return UserData::from(Auth::user())->include('favorite_song.name');
```

## Types of Lazy properties

### Conditional Lazy properties

You can include lazy properties in different ways:

```php
Lazy::create(fn() => SongData::collect($album->songs));
```

With a basic `Lazy` property, you must explicitly include it when the data object is transformed.

Sometimes you only want to include a property when a specific condition is true. This can be done with conditional lazy
properties:

```php
Lazy::when(fn() => $this->is_admin, fn() => SongData::collect($album->songs));
```

The property will only be included when the `is_admin` property of the data object is true. It is not possible to
include the property later on with the `include` method when a condition is not accepted.

### Relational Lazy properties

You can also only include a lazy property when a particular relation is loaded on the model as such:

```php
Lazy::whenLoaded('songs', $album, fn() => SongData::collect($album->songs));
```

Now the property will only be included when the song's relation is loaded on the model.

## Default included lazy properties

It is possible to mark a lazy property as included by default:

```php
Lazy::create(fn() => SongData::collect($album->songs))->defaultIncluded();
```

The property will now always be included when the data object is transformed. You can explicitly exclude properties that
were default included as such:

```php
AlbumData::create(Album::first())->exclude('songs');
```

## Auto Lazy

Writing Lazy properties can be a bit cumbersome. It is often a repetitive task to write the same code over and over
again while the package can infer almost everything.

Let's take a look at our previous example:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            Lazy::create(fn() => SongData::from($user->favorite_song))
        );
    }
}
```

The package knows how to get the property from the model and wrap it into a data object, but since we're using a lazy
property, we need to write our own magic creation method with a lot of repetitive code.

In such a situation auto lazy might be a good fit, instead of casting the property directly into the data object, the
casting process is wrapped in a lazy Closure.

This makes it possible to rewrite the example as such:

```php
#[AutoLazy]
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

While achieving the same result!

Auto Lazy wraps the casting process of a value for every property typed as `Lazy` into a Lazy Closure when the
`AutoLazy` attribute is present on the class.

It is also possible to use the `AutoLazy` attribute on a property level:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoLazy]
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

The auto lazy process won't be applied in the following situations:

- When a null value is passed to the property
- When the property value isn't present in the input payload and the property typed as `Optional`
- When a Lazy Closure is passed to the property

### Auto lazy with model relations

When you're constructing a data object from an Eloquent model, it is also possible to automatically create lazy
properties for model relations which are only resolved when the relation is loaded:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy]
        public Lazy|SongData $favoriteSong,
    ) {
    }
}
```

When the `favoriteSong` relation is loaded on the model, the property will be included in the data object.

If the name of the relation doesn't match the property name, you can specify the relation name:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy('favoriteSong')]
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

The package will use the regular casting process when the relation is loaded, so it is also perfectly possible to create a collection of data objects:

```php
class UserData extends Data
{
    /**
    * @param Lazy|array<int, SongData> $favoriteSongs
     */
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy]
        public Lazy|array $favoriteSongs,
    ) {
    }
}
```

## Only and Except

Lazy properties are great for reducing payloads sent over the wire. However, when you completely want to remove a
property Laravel's `only` and `except` methods can be used:

```php
AlbumData::from(Album::first())->only('songs'); // will only show `songs`
AlbumData::from(Album::first())->except('songs'); // will show everything except `songs`
```

It is also possible to use multiple keys:

```php
AlbumData::from(Album::first())->only('songs.name', 'songs.artist');
AlbumData::from(Album::first())->except('songs.name', 'songs.artist');
```

And special keys like described above:

```php
AlbumData::from(Album::first())->only('songs.{name, artist}');
AlbumData::from(Album::first())->except('songs.{name, artist}');
```

Only and except always take precedence over include and exclude, which means that when a property is hidden by `only` or
`except` it is impossible to show it again using `include`.

### Conditionally

It is possible to add an `include`, `exclude`, `only` or `except` if a certain condition is met:

```php
AlbumData::from(Album::first())->includeWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->excludeWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->onlyWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->except('songs', auth()->user()->isAdmin);
```

You can also use the values of the data object in such condition:

```php
AlbumData::from(Album::first())->includeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->excludeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->onlyWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->exceptWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
```

In some cases, you may want to define an include on a class level by implementing a method:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs' => $this->title === 'Together Forever',
        ];
    }
}
```

It is even possible to include nested properties:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs.title' => $this->title === 'Together Forever',
        ];
    }
}
```

You can define exclude, except and only partials on a data class:

- You can define **excludes** in a `excludeProperties` method
- You can define **except** in a `exceptProperties` method
- You can define **only** in a `onlyProperties` method

## Using query strings

It is possible to include or exclude lazy properties by the URL query string:

For example, when we create a route `my-account`:

```php
// in web.php

Route::get('my-account', fn() => UserData::from(User::first()));
```

We now specify that a key of the data object is allowed to be included by query string on the data object:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return ['favorite_song'];
    }

    // ...
}
```

Our JSON would look like this when we request `https://spatie.be/my-account`:

```json
{
    "name" : "Ruben Van Assche"
}
```

We can include `favorite_song` by adding it to the query in the URL as such:

```
https://spatie.be/my-account?include=favorite_song
```

```json
{
    "name" : "Ruben Van Assche",
    "favorite_song" : {
        "name" : "Never Gonna Give You Up",
        "artist" : "Rick Astley"
    }
}
```

We can also include multiple properties by separating them with a comma:

```
https://spatie.be/my-account?include=favorite_song,favorite_movie
```

Or by using a group input:

```
https://spatie.be/my-account?include[]=favorite_song&include[]=favorite_movie
```

Including properties works for data objects and data collections.

### Allowing includes by query string

By default, it is disallowed to include properties by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return [];
    }
}
```

You can pass several names of properties which are allowed to be included by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return ['favorite_song', 'name'];
    }
}
```

Or you can allow all properties to be included by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return null;
    }
}
```

### Other operations

It is also possible to run exclude, except and only operations on a data object:

- You can define **excludes** in `allowedRequestExcludes` and use the `exclude` key in your query string
- You can define **except** in `allowedRequestExcept` and use the `except` key in your query string
- You can define **only** in `allowedRequestOnly` and use the `only` key in your query string

## Mutability

Adding includes/excludes/only/except to a data object will only affect the data object (and its nested chain) once:

```php
AlbumData::from(Album::first())->include('songs')->toArray(); // will include songs
AlbumData::from(Album::first())->toArray(); // will not include songs
```

If you want to add includes/excludes/only/except to a data object and its nested chain that will be used for all future
transformations, you can define them in their respective *properties methods:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs'
        ];
    }
}
```

Or use the permanent methods:

```php
AlbumData::from(Album::first())->includePermanently('songs');
AlbumData::from(Album::first())->excludePermanently('songs');
AlbumData::from(Album::first())->onlyPermanently('songs');
AlbumData::from(Album::first())->exceptPermanently('songs');
```

When using a conditional includes/excludes/only/except, you can set the permanent flag:

```php
AlbumData::from(Album::first())->includeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
AlbumData::from(Album::first())->excludeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
AlbumData::from(Album::first())->onlyWhen('songs', fn(AlbumData $data) => count($data->songs) > 0), permanent: true);
AlbumData::from(Album::first())->except('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
```
