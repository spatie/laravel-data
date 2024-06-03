---
title: Use with Livewire
weight: 10
---

> Livewire is a full-stack framework for Laravel that makes building dynamic interfaces simple without leaving the
> comfort of Laravel.

Laravel Data works excellently with [Laravel Livewire](https://laravel-livewire.com).

You can use a data object as one of the properties of your Livewire component as such:

```php
class Song extends Component
{
    public SongData $song;

    public function mount(int $id)
    {
        $this->song = SongData::from(Song::findOrFail($id));
    }

    public function render()
    {
        return view('livewire.song');
    }
}
```

A few things are required to make this work:

1) You should implement `Wireable` on all the data classes you'll be using with Livewire
2) Each of these classes should also use the `WireableData` trait provided by this package
3) That's it

We can update our previous example to make it work with Livewire as such:

```php
class SongData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

## Livewire Synths (Experimental)

Laravel Data also provides a way to use Livewire Synths with your data objects. It will allow you to use data objects
and collections
without the need to make them Wireable. This is an experimental feature and is subject to change.

You can enable this feature by setting the config option in `data.php`:

```php
'livewire' => [
    'enable_synths' => false,
]
```

Once enabled, you can use data objects within your Livewire components without the need to make them Wireable:

```php
class SongUpdateComponent extends Component
{
    public SongData $data;

    public function mount(public int $id): void
    {
        $this->data = SongData::from(Song::findOrFail($id));
    }

    public function save(): void
    {
        Artist::findOrFail($this->id)->update($this->data->toArray());
    }

    public function render(): string
    {
        return <<<'BLADE'
        <div>
            <h1>Songs</h1>
            <input type="text" wire:model.live="data.title">
            <input type="text" wire:model.live="data.artist">
            <p>Title: {{ $data->title }}</p>
            <p>Artist: {{ $data->artist }}</p>
            <button wire:click="save">Save</button>
        </div>
        BLADE;
    }
}
```

### Lazy

It is possible to use Lazy properties, these properties will not be sent over the wire unless they're included. **Always
include properties permanently** because a data object is being transformed and then cast again between Livewire
requests the includes should be permanent.

It is possible to query lazy nested data objects, it is however not possible to query lazy properties which are not a data:

```php
use Spatie\LaravelData\Lazy;

class LazySongData extends Data
{
    public function __construct(
        public Lazy|ArtistData $artist,
        public Lazy|string $title,
    ) {}    
}
```

Within your Livewire view

```php
$this->data->artist->name; // Works
$this->data->title; // Does not work
```

### Validation

Laravel data **does not provide validation** when using Livewire, you should do this yourself! This is because laravel-data
does not support object validation at the moment. Only validating payloads which eventually become data objects.
The validation could technically happen when hydrating the data object, but this is not implemented 
because we cannot guarantee that every hydration happens when a user made sure the data is valid
and thus the payload should be validated.
