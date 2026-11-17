# Changelog

All notable changes to `novius/laravel-filament-translatable` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/novius/laravel-filament-translatable/compare/1.0.1...HEAD)

### Changed

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
