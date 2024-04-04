---
title: Use with Inertia
weight: 9
---

> Inertia.js lets you quickly build modern single-page React, Vue, and Svelte apps using classic server-side routing and controllers.

Laravel Data works excellent with [Inertia](https://inertiajs.com).

You can pass a complete data object to an Inertia response:

```php
return Inertia::render('Song', SongsData::from($song));
```

## Lazy properties

This package supports [lazy](https://spatie.be/docs/laravel-data/v4/as-a-resource/lazy-properties) properties, which can be manually included or excluded.

Inertia has a similar concept called [lazy data evaluation](https://inertiajs.com/partial-reloads#lazy-data-evaluation), where some properties wrapped in a closure only get evaluated and included in the response when explicitly asked.

This package can output specific properties as Inertia lazy props as such:

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
            Lazy::inertia(fn() => $song->title),
            Lazy::closure(fn() => $song->artist)
        );
    }
}
```

We provide two kinds of lazy properties:

- **Lazy::inertia()** Never included on first visit, optionally included on partial reloads
- **Lazy::closure()** Always included on first visit, optionally included on partial reloads

Now within your JavaScript code, you can include the properties as such:

```js
router.reload((url, {
    only: ['title'],
});
```
