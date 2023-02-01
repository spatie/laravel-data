# Changelog

All notable changes to `laravel-data` will be documented in this file.

## 3.0.0 - 2023-02-01

- The validation logic is completely rewritten
    - Add support for nested nullable and optional data objects
    - Allow referencing other fields in validation attributes when the data is nested
    - Allow referencing url parameters (and even model properties) in validation attributes
    - Allow circular dependencies when validating data
    - Add support for nested validation messages and attributes
- Package specific properties are renamed to avoid conflicts
- Serialization logic updated to only serialize your properties
- Prevent fatal error when passing a string containing only an integer to JSON Normalizer (#322)
- Ignore leading ! in DateTimeInterfaceTransformer (#325)
- Extend the make:data command to be more flexible (#335)
  
## 2.2.3 - 2023-01-24

- Add Laravel 10.x support (#331)

## 2.2.2 - 2023-01-09

- Add a way to prepend DataPipes to the pipeline (#305)
- Better IDE completion (#307)
- Make eloquent collection cast respect collection (#308)

## 2.2.1 - 2022-12-21

- fix support to return 201 status code in Json Responses (#291)

## 2.2.0 - 2022-12-21

- Add generic return type for DataCollection::toCollection (#290)
- Improve ide.json completion (#294)
- Pass in payload relative to the data object when generating rules (#264)
- ignore phpstorm attributes when instantiating and add readonly property (#281)

## 2.1.0 - 2022-12-07

- Stop using custom pipeline when creating data magically from requests
- set timezones in datetime casts (#287 )

## 2.0.16 - 2022-11-18

- add support for optional properties in TypeScript transformer (#153)

## 2.0.15 - 2022-11-17

- refactor test suite to Pest (#239)
- update the docs
- add a `StudlyMapper`
- add better support for Livewire

## 2.0.14 - 2022-11-10

- accept 'float' as data type (#237)
- fix typo in mime type validation rule(#243)
- add support for enums in validation attributes
- add support for withoutTrashed to exists validation attribute (#248)
- add PHP 8.2 testing in GH actions
- add ability to modify properties before the data pipeline (#247)

## 2.0.13 - 2022-10-14

- fix first and last page url are never null (#215)
- add ability to statically retrieve a data models rules (#221)
- improved pattern matching in DataCollectionAnnotationReader (#225)
- add ExcludeWithout attribute rule (#230)
- improve getValidationRules to also retrieve wildcard rules (#231)
- return property with or without mapping the name (#199)

## 2.0.12 - 2022-09-29

- Improve pattern matching in DataCollectionAnnotationReader (#214)
- Add ability to retrieve rules for a data object (#221)

## 2.0.11 - 2022-09-28

- Use generics with Data::collection (#213)
- Improve pattern matching in DataCollectionAnnotationReader (#214)
- Fix TypeScript Transformer paginated collections, first and last page url are never null (#215)

## 2.0.10 - 2022-09-07

- Add support for a JsonSerializer
- Fix generic iterator definition for data collections
- Fix validation of DataCollection objects not working as expected

## 2.0.9 - 2022-08-23

- Add support for timezone manipulation in casts and transformers (#200)

## 2.0.8 - 2022-08-18

- use AbstractCloner instead of reflection (#196)

## 2.0.7 - 2022-08-18

- Add var dumper caster (#195)
- Allow direct pipeline call (#193)
- Rename ArrayableNormalizer (#191)
- Make normalizers configurable (#190)

## 2.0.6 - 2022-08-12

- Add cast lazy condition result to bool (#186)
- Add conditional return types for collections (#184)
- Fix windows tests

## 2.0.5 - 2022-08-10

- Fix validation rules not being used for data collections and objects

## 2.0.4 - 2022-08-01

- Add IDE completion support (#182)

## 2.0.3 - 2022-07-29

- Add support for invokable rules (#179)

## 2.0.2 - 2022-07-29

- more consistent use of transform through codebase for transforming collections and data

## 2.0.1 - 2022-07-13

- Add class defined partials (#164)
- Use mapped property names in TypeScript (#154)
- Add make:data command (#157)
- Better support for overwritten rules
- Add support for Lazy inertia props (#163)
- Add support for array query parameters as partials (#162)

## 2.0.0 - 2022-07-08

Version 2 of laravel-data is a complete overhaul, we've almost completely rewritten the package.

This is a (non-complete) list of the new features:

- A DataPipeline
- Data normalizers
- Mappable property names
- Wrapping of data in responses
- `only` and `except` methods on Data and DataCollections
- Multiple parameter magic methods
- Optional properties
- Split DataCollections
- Better support for TypeScript Transformer
- And a lot more ...

## 1.5.1 - 2022-07-08

- Fix optional parameters (#152)
- Use protected properties and methods (#147 )

## 1.5.0 - 2022-05-25

## What's Changed

- add values() on DataCollection by @Nielsvanpach in https://github.com/spatie/laravel-data/pull/135

## New Contributors

- @Nielsvanpach made their first contribution in https://github.com/spatie/laravel-data/pull/135

**Full Changelog**: https://github.com/spatie/laravel-data/compare/1.4.7...1.5.0

## 1.4.7 - 2022-05-16

- support $payload as a dependency in rules (#123)

## 1.4.6 - 2022-04-06

- Add Dependency Injection for rules, messages, and attributes methods (#114)

## 1.4.5 - 2022-03-18

- Add support for stdClass payload casting (#106)

## 1.4.4 - 2022-03-18

- use present validation rule instead of required for data collections

## 1.4.3 - 2022-02-16

- allow using default password config in password validation attribute (#94)
- solve binding issues on Laravel Octane (#101)
- fixes a bug where models nested by relation could not be created due to date casts
- add a `links` array to the paginated response
- stop execution of `lazy::whenLoaded` closure when the relation is `null`

## 1.4.2 - 2022-01-26

- fix for aborting value assignment after a false boolean (#80)
- add a `WithoutValidation` attribute
- allow transformers to target native types, data collections and data objects

## 1.4.1 - 2022-01-21

- Allow transformers to target Data and DataCollections

## 1.4.0 - 2022-01-20

- removes checks for built in types and `isBuiltIn` from `DataProperty`
- add better support for defaults

## 1.3.3 - 2022-01-20

## What's Changed

- Laravel 9.x by @aidan-casey in https://github.com/spatie/laravel-data/pull/77
- Removes Spatie's Laravel Enums from dev requirements by @aidan-casey in https://github.com/spatie/laravel-data/pull/76

## New Contributors

- @aidan-casey made their first contribution in https://github.com/spatie/laravel-data/pull/77

**Full Changelog**: https://github.com/spatie/laravel-data/compare/1.3.2...1.3.3

## 1.3.2 - 2022-01-19

- add support for json_encode to Data objects
- add support for json_encode to DataCollection objects

## 1.3.1 - 2022-01-07

- add basic support for intersection types
- allow casting of built in PHP types
- add support for inferring enum rules
- fix an issue where an Enum validation attribute would not work

## 1.2.5 - 2021-12-29

- fixes the RequiredRuleResolver to support custom rules like `Enum`
- add an Enum validation rule attribute

## 1.2.4 - 2021-12-16

- rename the `authorized` method to `authorize`
- disable the behavior were excluded conditional properties still could be included

## 1.2.3 - 2021-12-03

- fix return type notice message

## 1.2.1 - 2021-11-19

- fixes an issue where data object could not be created when it had lazy nested data object
- fixes windows test suite

## 1.2.0 - 2021-11-16

- when creating data objects, we now will always run validation when a `Request` object is given not only when a data object is injected
- removal of `DataFromRequestResolver`
- added `DataValidatorResolver`

## 1.1.0 - 2021-11-12

- change data property types collection checking procedure
- move `spatie/test-time` dependency to require-dev
- expand support for nested data object creation (#19)
- expand support for annotating data collections

## 1.0.4 - 2021-11-04

- revert allow ignoring with a closure within a unique rule

## 1.0.3 - 2021-11-02

- allow ignoring with a closure within a unique rule

## 1.0.2 - 2021-11-02

- add a `WithData` trait for quicker getting data from objects

## 1.0.1 - 2021-10-28

- fix required rules being added when not allowed

## 1.0.0 - 2021-10-27

- initial release
