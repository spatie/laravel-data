---
title: Internal structures
weight: 6
---

This package has some internal structures which are used to analyze data objects and their properties. They can be useful when writing casts, transformers or rule inferrers.

## DataClass

The DataClass represents the structure of a data object and has the following methods:

- `properties` get all the `DataProperty`'s of the object (more on that later)
- `creationMethods` get all magical creation methods and the types that can be passed as a parameter to them
- `reflection` get the `ReflectionClass` object for the data class

## DataProperty

A data property represents a single property within a data object, you can call the following methods on it:

- `isLazy` check if the property can be lazy evaluated
- `isNullable` check if the property can be null
- `isBuiltIn` check if the property has a built-in PHP type
- `isData` check if the property has a data object type
- `isDataCollection` check if the property has a data collection type
- `types` get all the types the property can have
- `name` get the name of the property
- `className` get the name of the class of the property
- `validationAttributes` get all the validation attributes associated with the property
- `castAttribute` get the cast attribute associated with the property
- `transformerAttribute` get the transformer attribute associated with the property
- `dataClassName` get the class of the data object(s) stored within the property

