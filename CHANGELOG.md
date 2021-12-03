# Changelog

All notable changes to `laravel-data-resource` will be documented in this file.

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
