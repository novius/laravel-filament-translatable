# Changelog

All notable changes to `novius/laravel-filament-translatable` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/novius/laravel-filament-translatable/compare/1.2.0...HEAD)

## [1.2.0](https://github.com/novius/laravel-filament-translatable/compare/1.1.0...1.2.0) - 2026-11-18

### Added

- `TranslationsActionGroup`, a dropdown button for the header of the view or edit page of a translatable model: it
  opens the other translations of the record (on the view or edit page, as authorized), then creates the missing ones
  with the create page of the package. Placed by the `HasTranslationsActionGroup` trait of a page
  (`translationsActionGroup()`), or built with `TranslationsActionGroup::forModel()`.

### Changed

- `TranslationsColumn` shares the links of `TranslationsActionGroup`: a translation links to its edit page, or to its
  view page when the edit page is missing or not authorized, and a missing translation to the create page only when the
  user can create; otherwise, the flag has no link. The resource is the one of the page (nested resources included). A
  locale not installed (laravel-lang) is named, as in `LocaleColumn`. **Upgrade note**: the `translations` view
  receives prepared `translations` (`exists`, `deleted`, `label`, `flag`, `url`) instead of `resource`, `record` and
  `locales`.

## [1.1.0](https://github.com/novius/laravel-filament-translatable/compare/1.0.1...1.1.0) - 2026-11-17

### Added

- `CommonRecordSection`, the fields of the common record of a translatable model using `HasCommonRecord`
  (laravel-translatable 2.1), bound to its `commonRecord` relationship. With the `CreateRecord` page of the package, an
  original saves its common record from these fields before itself, so that the common record gets every value of the
  form, required ones included, in a database transaction with the original; a new translation shares the common record
  of its parent, the section showing a notice instead of its fields. Several sections of the common record are saved
  together; a hidden or disabled section is not saved, the model creating its common record with
  `createCommonRecord()`.
- `CreateRecord::copiedOnTranslate()`: the attributes of the parent copied to a new translation, all of them when it
  returns `null` (by default).
- `CreateRecord::translationStartsAsDraft()`: whether a new translation of a publishable model starts as a draft (`true`
  by default).

### Changed

- Requires `novius/laravel-translatable` ^2.1.
- A new translation of a publishable model (laravel-publishable) starts as a draft, without the publication dates of its
  parent. **Upgrade note**: it copied the publication of its parent; override `translationStartsAsDraft()` to return
  `false` to keep it.
- `CreateRecord` overrides `handleRecordCreation()` to save the common record first. **Upgrade note**: a create page
  overriding it must call `parent::handleRecordCreation()` to keep this behavior.
- Use the Laravel package skeleton: Composer scripts (`test`, `test:unit`, `lint`, `lint:check`, `analyse`), Pest tests
  under `tests/Feature` with Orchestra Testbench, `phpstan.neon.dist`, `phpunit.xml.dist`, a `tests` CI workflow on
  Ubuntu and Windows (PHP 8.2 to 8.5, Laravel 12 and 13, Filament 4 and 5), the update-changelog workflow, release notes
  config, dependabot, contributing and security docs, and the package agent skills.
- PHPStan at level 8 (level 1 before). The `IsTranslatable` trait gives the new `modelTranslatableConfig()`, the
  translatable config of the model of the component, used by the field, the columns, the filter and the create page;
  `translatableModel()` takes a `class-string<Model>` and returns `?Model`.

## [1.0.1](https://github.com/novius/laravel-filament-translatable/compare/1.0.0...1.0.1) - 2026-05-12

### Added

- Laravel 13 compatibility.

## [1.0.0](https://github.com/novius/laravel-filament-translatable/releases/tag/1.0.0) - 2026-02-18

### Added

- Initial release, for Filament 4 and 5 and `novius/laravel-translatable` 2: the `Locale` form field, the
  `LocaleColumn` and `TranslationsColumn` table columns, the `LocaleFilter` table filter, and the `CreateRecord` page
  creating a translation of a record from the `locale_parent` and `locale` query parameters.
