---
title: Use with Inertia
weight: 9
---

> Inertia.js lets you quickly build modern single-page React, Vue, and Svelte apps using classic server-side routing and
> controllers.

Laravel Data works excellent with [Inertia](https://inertiajs.com).

You can pass a complete data object to an Inertia response:

```php
return Inertia::render('Song', SongsData::from($song));
```

## Lazy properties

This package supports [lazy](https://spatie.be/docs/laravel-data/v4/as-a-resource/lazy-properties) properties, which can
be manually included or excluded.

Inertia has a similar concept called [lazy data evaluation](https://inertiajs.com/partial-reloads#lazy-data-evaluation),
where some properties wrapped in a closure only get evaluated and included in the response when explicitly asked.
Inertia v2 introduced the concept of [deferred props](https://inertiajs.com/deferred-props), which allows to defer the
loading of certain data until after the initial page render.

This package can output specific properties as Inertia lazy or deferred props as such:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
        public Lazy|string $lyrics,
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            Lazy::inertia(fn() => $song->title),
            Lazy::closure(fn() => $song->artist)
            Lazy::inertiaDeferred(fn() => $song->lyrics)
        );
    }
}
```

We provide three kinds of lazy properties:

- **Lazy::inertia()** Never included on first visit, optionally included on partial reloads
- **Lazy::closure()** Always included on first visit, optionally included on partial reloads
- **Lazy::inertiaDeferred()** Included when ready, optionally included on partial reloads

Now within your JavaScript code, you can include the properties as such:

```js
router.reload((url, {
    only: ['title'],
});
```

#### Deferred property groups

It is possible to group deferred properties together, so that all grouped properties are loaded at the same time. In
order to achieve this, you can pass a group name as the second argument to `Lazy::inertiaDeferred()`:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
        public Lazy|string $lyrics,
    ) {
    }
    
    public static function fromModel(Song $song): self
    {
        return new self(
            Lazy::inertiaDeferred(fn() => $song->title),
            Lazy::inertiaDeferred(fn() => $song->artist, 'details')
            Lazy::inertiaDeferred(fn() => $song->lyrics, 'details')
        );
    }
}
```

### Auto lazy Inertia properties

We already saw earlier that the package can automatically make properties Lazy, the same can be done for Inertia
properties.

It is possible to rewrite the previous example as follows:

```php
use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Attributes\AutoInertiaLazy;
use Spatie\LaravelData\Attributes\AutoInertiaDeferred;

class SongData extends Data
{
    public function __construct(
        #[AutoInertiaLazy]
        public Lazy|string $title,
        #[AutoClosureLazy]
        public Lazy|string $artist,
        #[AutoInertiaDeferred]
        public Lazy|string $lyrics,
    ) {
    }
}
```

If all the properties of a class should be either Inertia or closure lazy, you can use the attributes on the class
level:

```php
#[AutoInertiaLazy]
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
    ) {
    }
}
```

#### Deferred property groups

For the `AutoInertiaDeferred` attribute, it is also possible to specify a group name in order to group deferred
properties together:

```php
class SongData extends Data
{
    public function __construct(
        #[AutoInertiaDeferred()]
        public Lazy|string $title,
        #[AutoInertiaDeferred('details')]
        public Lazy|string $artist,
        #[AutoInertiaDeferred('details')]
        public Lazy|string $lyrics,
    ) {
    }
}
```
