# Changelog

## [Unreleased]

## [1.2.0] - 2016-04-09

### Added

- Support for fallback locale when using `RRule::humanReadable()` [#11](https://github.com/rlanvin/php-rrule/pull/11)
- Dutch translation (NL) [#9](https://github.com/rlanvin/php-rrule/pull/9)

### Fixed

- Fixed fatal error Locale class not found when intl extension is not loaded [#10](https://github.com/rlanvin/php-rrule/issues/10)

## [1.1.0] - 2016-03-30

### Added

- New class `RRule\RSet` (recurrence set) that can combine multiple RRULE, EXRULE, RDATE and EXDATE. [#7](https://github.com/rlanvin/php-rrule/issues/7)
- New interface `RRule\RRuleInterface` to unify `RRule` and `RSet`
- New methods: `isFinite()`, `isInfinite()`

### Fixed

- Fix bug preventing the iteration of multiple instances of RRule at the same time
- Fix occursAt failing when the date passed was a different timezone [#8](https://github.com/rlanvin/php-rrule/pull/8)
- Fix bug at WEEKLY frequency with a partially filled cache in some circumstances
- Fix various reference bugs causing corruption of the cache in some circumstances (related to DateTime object being mutable)

### Removed

- The alias `RRule::occursOn` has been removed (use `occursAt` instead)

## [1.0.1] - 2016-03-11

### Fixed

- Ensure the results are returned in the same timezone as DTSTART. [#6](https://github.com/rlanvin/php-rrule/issues/6)
- LogicException namespacing bug. [#3](https://github.com/rlanvin/php-rrule/issues/3)

## 1.0.0 - 2016-03-02

### Added

- First release, everything before that was unversioned (`dev-master` was used).

[Unreleased]: https://github.com/rlanvin/php-rrule/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/rlanvin/php-rrule/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/rlanvin/php-rrule/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/rlanvin/php-rrule/compare/v1.0.0...v1.0.1
