---
title: Transforming data
weight: 3
---

Each property of a data object should be transformed into a usable type to communicate via JSON. For example, a `Carbon` object can be transformed to `16-05-1994`, `16-05-1994T00:00:00+00` or something completely different.

For the default types (string, bool, int, float and array) there are now complex transformations required, but special types like `Carbon` or a Laravel Model need a special transformer.

In the end 
