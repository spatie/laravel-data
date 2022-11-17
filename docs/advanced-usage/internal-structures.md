---
title: Internal structures
weight: 11
---

This package has some internal structures which are used to analyze data objects and their properties. They can be helpful when writing casts, transformers or rule inferrers.

## DataClass

The DataClass represents the structure of a data object and has the following properties:

- `name` the name of the data class
- `properties` all the `DataProperty`'s of the class (more on that later)
- `methods` all the magical creation `DataMethod`s of the class (more on that later)
- `constructorMethod` the constructor `DataMethod` of the class

## DataProperty

A data property represents a single property within a data object.

- `name` the name of the property
- `className` the name of the class of the property
- `type` the `DataType` of the property (more on that later) 
- `validate` should the property be automatically validated 
- `isPromoted` is the property constructor promoted
- `hasDefaultValue` has the property a default value
- `defaultValue` the default value of the property
- `cast` the cast assigned to the property
- `transformer` the transformer assigned to the property
- `inputMappedName` the name used to map a property name given
- `outputMappedName` the name used to map a property name onto
- `attributes` a collection of `ReflectionAttribute`s assigned to the property

## DataMethod

A data method represents a method within a data object.

- `name` the name of the method
- `parameters` all the `DataParameter`'s of the class (more on that later)
- `isStatic` whether the method is static
- `isPublic` whether the method is public
- `isCustomCreationMethod` whether the method is a custom creation method (=magical creation method)

## DataParameter

A data parameter represents a single parameter/property within a data method.

- `name` the name of the parameter
- `hasDefaultValue` has the parameter a default value
- `defaultValue` the default value of the parameter
- `isPromoted` is the property/parameter constructor promoted
- `type` the `DataType` of the parameter (more on that later)

## DataType
- `isNullable` can the type be nullable
- `isMixed` is the type a mixed type
- `isLazy` can the type be lazy
- `isOptional` can the type be optional
- `isDataObject` is the type a data object
- `isDataCollectable` is the type a data collection
- `dataClass` the class of the data object/collection
- `acceptedTypes` an array of types accepted by this type + their base types

