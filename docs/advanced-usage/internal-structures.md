---
title: Internal structures
weight: 11
---

This package has some internal structures which are used to analyze data objects and their properties. They can be
helpful when writing casts, transformers or rule inferrers.

## DataClass

The DataClass represents the structure of a data object and has the following properties:

- `name` the name of the data class
- `properties` all the `DataProperty`'s of the class (more on that later)
- `methods` all the magical creation `DataMethod`s of the class (more on that later)
- `constructorMethod` the constructor `DataMethod` of the class
- `isReadOnly` is the class read only
- `isAbstract` is the class abstract
- `appendable` is the class implementing `AppendableData`
- `includeable` is the class implementing `IncludeableData`
- `responsable` is the class implementing `ResponsableData`
- `transformable` is the class implementing `TransformableData`
- `validatable` is the class implementing `ValidatableData`
- `wrappable` is the class implementing `WrappableData`
- `emptyData` the the class implementing `EmptyData`
- `attributes` a collection of resolved attributes assigned to the class
- `dataCollectablePropertyAnnotations` the property annotations of the class used to infer the data collection type
- `allowedRequestIncludes` the allowed request includes of the class
- `allowedRequestExcludes` the allowed request excludes of the class
- `allowedRequestOnly` the allowed request only of the class
- `allowedRequestExcept` the allowed request except of the class
- `outputMappedProperties` properties names which are mapped when transforming the data object
- `transformationFields` structure of the transformation fields

## DataProperty

A data property represents a single property within a data object.

- `name` the name of the property
- `className` the name of the class of the property
- `type` the `DataPropertyType` of the property (more on that later)
- `validate` should the property be automatically validated
- `computed` is the property computed
- `hidden` will the property be hidden when transforming the data object
- `isPromoted` is the property constructor promoted
- `isReadOnly` is the property read only
- `hasDefaultValue` has the property a default value
- `defaultValue` the default value of the property
- `cast` the cast assigned to the property
- `transformer` the transformer assigned to the property
- `inputMappedName` the name used to map a property name given
- `outputMappedName` the name used to map a property name onto
- `attributes` a collection of resolved attributes assigned to the property

## DataMethod

A data method represents a method within a data object.

- `name` the name of the method
- `parameters` all the `DataParameter`'s and `DataProperty`s of the method (more on that later)
- `isStatic` whether the method is static
- `isPublic` whether the method is public
- `isCustomCreationMethod` whether the method is a custom creation method (=magical creation method)
- `returnType` the `DataType` of the return value (more on that later)

## DataParameter

A data parameter represents a single parameter/property within a data method.

- `name` the name of the parameter
- `isPromoted` is the property/parameter constructor promoted
- `hasDefaultValue` has the parameter a default value
- `defaultValue` the default value of the parameter
- `type` the `DataType` of the parameter (more on that later)

## DataType

A data type represents a type within a data object.

- `Type` can be a `NamedType`, `UnionType` or `IntersectionType` (more on that later)
- `isNullable` can the type be nullable
- `isMixed` is the type a mixed type
- `kind` the `DataTypeKind` of the type (more on that later)

## DataPropertyType

Extends from the `DataType` and has the following additional properties:

- `isOptional` can the type be optional
- `lazyType` the class of the lazy type for the property
- `dataClass` the data object class of the property or the data object class of the collection it collects
- `dataCollectableClass` the collectable type of the data objects
- `kind` the `DataTypeKind` of the type (more on that later)

## DataTypeKind

An enum representing the kind of type of a property/parameter with respect to the package:

- Default: a non package specific type
- DataObject: a data object
- DataCollection: a `DataCollection` of data objects
- DataPaginatedCollection: a `DataPaginatedCollection` of data objects
- DataCursorPaginatedCollection: a `DataCursorPaginatedCollection` of data objects
- DataArray: an array of data objects
- DataEnumerable: a `Enumerable` of data objects
- DataPaginator: a `Paginator` of data objects
- DataCursorPaginator: a `CursorPaginator` of data objects

## NamedType

Represents a named PHP type with the following properties:

- `name` the name of the type
- `builtIn` is the type a built-in type
- `acceptedTypes` an array of accepted types as string
- `kind` the `DataTypeKind` of the type (more on that later)
- `dataClass` the data object class of the property or the data object class of the collection it collects
- `dataCollectableClass` the collectable type of the data objects
- `isCastable` wetter the type is a `Castable`

## UnionType / IntersectionType

Represents a union or intersection of types with the following properties:

- `types` an array of types (can be `NamedType`, `UnionType` or `IntersectionType`)

