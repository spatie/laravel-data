---
title: Use with Livewire
weight: 10
---

> Livewire is a full-stack framework for Laravel that makes building dynamic interfaces simple without leaving the comfort of Laravel.

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
