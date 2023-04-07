---
title: Custom collections
weight: 12
---

Laravel-data ships with three collections:

- **DataCollection** (used for collecting arrays with data )
- **PaginatedDataCollection** (used for collecting paginators with data)
- **CursorPaginatedDataCollection** (used for collecting cursor paginators with data)

When calling the `collection` method on a data object, one of these collections will be returned depending on the value given to the method.

It is possible to return a custom collection in such a scenario. First, you must create a new collection class extending from `DataCollection`, `PaginatedDataCollection`, or `CursorPaginatedDataCollection` depending on what kind of collection you want to build. You can add methods and properties, but the constructor should keep the same signature.

Next, you need to define that your custom collection should be used when collecting data. Which can be done by setting one of the collection properties in your data object:

```php
class SongData extends Data
{
    protected static string $_collectionClass = MyCustomDataCollection::class;

    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

For a `PaginatedDataCollection`, you need to set the `$_paginatedCollectionClass` property, and for a `CursorPaginatedDataCollection` the `$_cursorPaginatedCollectionClass` property.
