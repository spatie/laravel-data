# Changelog

All notable changes to `laravel-data` will be documented in this file.

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
