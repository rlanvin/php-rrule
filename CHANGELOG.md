# Changelog

## [Unreleased]

## [2.4.0] - 2023-01-06

### Fixed

- Exclude files from dist packages [#110](https://github.com/rlanvin/php-rrule/pull/110)
- Improve German translation [#112](https://github.com/rlanvin/php-rrule/issues/112)
- Daylight Saving Time issue with PHP 8.1 [#120](https://github.com/rlanvin/php-rrule/issues/120)

### Added

- Added Portugese translation [#108](https://github.com/rlanvin/php-rrule/pull/108)
- Added Polish translation [#106](https://github.com/rlanvin/php-rrule/pull/106)

## [2.3.2] - 2022-05-03

### Fixed

- Fix timezone (and the entire rule) changed to uppercase if rule was created using `createdFromRfcString` [#103](https://github.com/rlanvin/php-rrule/issues/103)

## [2.3.1] - 2022-04-22

### Fixed

- Fix microseconds not always removed from dtstart, causing date comparison issues with specific date input [#104](https://github.com/rlanvin/php-rrule/issues/104)

## [2.3.0] - 2021-10-25

### Added

- Added Swedish translation [#96](https://github.com/rlanvin/php-rrule/pull/96/)
- Added `bin/review_translations.php` as a helper for translators and contributors
- Added Hebrew translation [#95](https://github.com/rlanvin/php-rrule/pull/95)

### Fixed

- Fix Finnish translation [#94](https://github.com/rlanvin/php-rrule/issues/94)
- Update French translation
- Update German translation
- Fix compatibility with PHP 8.1 [#100](https://github.com/rlanvin/php-rrule/pull/100)

## [2.2.2] - 2021-01-09

### Fixed

- Fix `getOccurrencesAfter` returns empty array when `$inclusive` is `false` and `$limit` is not set [#93](https://github.com/rlanvin/php-rrule/pull/93)

## [2.2.1] - 2020-12-09

### Fixed

- Fix support for `DateTimeImmutable` [#90](https://github.com/rlanvin/php-rrule/issues/90)

## [2.2.0] - 2019-11-01

### Added

- Persian (Farsi) translation [#72](https://github.com/rlanvin/php-rrule/pull/72)
- Support for Windows timezone [#69](https://github.com/rlanvin/php-rrule/issues/69)

### Fixed

- Fix valid rules wrongly detected as not producing results, and cut short after MAX_CYCLES [#78](https://github.com/rlanvin/php-rrule/issues/78)
- Fix `RRule::createFromRfcString` not detecting RSet properly if the rule was lowercase
- [internal] Replace static variables by array constants (permitted since PHP 5.6). Shouldn't break backward compatibility unless you were doing weird things with this lib in the first place.

## [2.1.0] - 2019-05-30

### Fixed

- Fix locale format for i18n files without `intl` extension [#67](https://github.com/rlanvin/php-rrule/issues/67)

### Added

- Added new methods to `RSet`: `removeDate`, `clearDates`, `removeExDate` and `clearExDates` [#66](https://github.com/rlanvin/php-rrule/pull/66)

## [2.0.0] - 2019-03-16

- Add support for PHP 7.3

## [2.0.0-rc1] - 2019-01-13

- Rewrite the core algorithm to use a native PHP generator, drop compability with PHP < 5.6 [#43](https://github.com/rlanvin/php-rrule/issues/43)

### Added

- New option `custom_path` to `humanReadable()` to use custom translation files [#56](https://github.com/rlanvin/php-rrule/issues/56)
- New helpers methods [#60](https://github.com/rlanvin/php-rrule/issues/60)
  - `getOccurrencesBefore`
  - `getOccurrencesAfter`
  - `getNthOccurrencesBefore`
  - `getNthOccurrencesAfter`
  - `getNthOccurrencesFrom`

## [1.6.3] - 2019-01-13

### Fixed

- Fix error when timezone is an offset instead of an olson name. [#61](https://github.com/rlanvin/php-rrule/issues/61)
- Fix VALUE is a valid param of EXDATE [#62](https://github.com/rlanvin/php-rrule/issues/62)

## [1.6.2] - 2018-09-18

### Fixed

- Fix typo in NL translation [#53](https://github.com/rlanvin/php-rrule/issues/53)

## [1.6.1] - 2018-04-13

### Fixed

- Null check to prevent 0 (false) values being lost when exporting to RFC string [#50](https://github.com/rlanvin/php-rrule/pull/50)
- Fix warning in `humanReadable()` [#44](https://github.com/rlanvin/php-rrule/pull/44)
- Fix typo in NL translation [#46](https://github.com/rlanvin/php-rrule/pull/46)

## [1.6.0] - 2017-10-11

### Added

- German translation [#38](https://github.com/rlanvin/php-rrule/pull/38)
- Add `include_until` option to `humanReadable` to omit the "until" part of the rule [#36](https://github.com/rlanvin/php-rrule/pull/36)

## [1.5.1] - 2017-05-15
### Fixed

- Throw exception when passing a float instead of an int (e.g. INTERVAL=1.5) instead of casting silently
- Variable name typo [#34](https://github.com/rlanvin/php-rrule/issues/34)

## [1.5.0] - 2017-05-07

### Added

- Add `explicit_infinite` and `include_start` options to `humanReadable` to respectivity omit "forever" and the start date from the sentence.
- RSet constructor now accepts a string to build a RSET from a RFC string [#26](https://github.com/rlanvin/php-rrule/issues/26)
- New factory method `RRule::createFromRfcString()` to build either a RRule or a RSet from a string
- Add a `$limit` parameter to `getOccurrences()` and `getOccurrencesBetween()` to make working with infinite rule easier
- Add a `$dtstart` parameter to `RRule` and `RSet` constsructor to specify dtstart when working with a RFC string without DTSTART.

### Fixed

- When creating a RRule, the RFC parser will not accept multiple DTSTART or RRULE lines

### Deprecated

- `RRule::parseRfcString` is deprecated. Note: it wasn't part of the documentation in the first place, but just in case someone is using it, it's not removed yet.

## [1.4.2] - 2017-03-29

### Fixed

- `humanReadable()` fails if the RRule was created from a RFC string containing the timezone (e.g. `DTSTART;TZID=America/New_York:19970901T090000`)

## [1.4.1] - 2017-02-02

### Fixed

- `RRule::offsetGet` and `RSet::offsetGet` throw `InvalidArgumentException` for illegal offset types [#22](https://github.com/rlanvin/php-rrule/issues/22)
- Update exception message for UNTIL parse error [#23](https://github.com/rlanvin/php-rrule/pull/23)
- Fix parser handling of UNTIL when DTSTART is not provided [#25](https://github.com/rlanvin/php-rrule/issues/25)
- Accept invalid RFC strings generated by the JS lib but triggers a Notice message [#25](https://github.com/rlanvin/php-rrule/issues/25)
- Rework `RRule::i18nLoad()` to accept locales such as `en_sg` and use `Locale::parseLocale` when possible [#24](https://github.com/rlanvin/php-rrule/issues/24)
- Fix `humanReadable` fails with `intl` enabled when the timezone is "Z" [#24](https://github.com/rlanvin/php-rrule/issues/24)

## [1.4.0] - 2016-11-11

### Added

- Add `RRule::getRule()` method to return original rule array [#17](https://github.com/rlanvin/php-rrule/pull/17)
- Add `RSet::getRRules()`, `RSet::getExRules()`, `RSet::getDates()` and `RSet::getExDates()`
- Tests for PHP 7.0

### Fixed

- Fix a bug in `rfcString` when using a frequency constant (instead of a string) to create the rule [#16](https://github.com/rlanvin/php-rrule/pull/16)
- Fix a undefined index bug in RFC parser

## [1.3.1] - 2016-08-09

### Added

- Italian translation (it) [#14](https://github.com/rlanvin/php-rrule/pull/14)

### Fixed

- Fixed a bug when combining values with an integer modifier and regular values in `BYDAY` (example `1MO,FR`)
- Fixed RRule created with a timestamp start date generates an invalid RFC string [#15](https://github.com/rlanvin/php-rrule/issues/15)

## [1.3.0] - 2016-07-08

### Added

- Spanish translation (es) [#12](https://github.com/rlanvin/php-rrule/pull/12)
- `$include_timezone` parameter to `RRule::rfcString()` to produce a RFC string without timezone information

### Fixed

- `RRule::parseRfcString()` is strictier and will not accept invalid `DTSTART` and `UNTIL` formats (use the array syntax in the constructor with `DateTime` objects if you need to create rules with complex combinations of timezones). [#13](https://github.com/rlanvin/php-rrule/issues/13)

## [1.2.0] - 2016-04-09

### Added

- Support for fallback locale when using `RRule::humanReadable()` [#11](https://github.com/rlanvin/php-rrule/pull/11)
- Dutch translation (nl) [#9](https://github.com/rlanvin/php-rrule/pull/9)

### Fixed

- Fixed fatal error Locale class not found when intl extension is not loaded [#10](https://github.com/rlanvin/php-rrule/issues/10)

## [1.1.0] - 2016-03-30

### Added

- New class `RRule\RSet` (recurrence set) that can combine multiple RRULE, EXRULE, RDATE and EXDATE. [#7](https://github.com/rlanvin/php-rrule/issues/7)
- New interface `RRule\RRuleInterface` to unify `RRule` and `RSet`
- New methods: `isFinite()`, `isInfinite()`

### Fixed

- Fix bug preventing the iteration of multiple instances of RRule at the same time
- Fix `occursAt` failing when the date passed was a different timezone [#8](https://github.com/rlanvin/php-rrule/pull/8)
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

[Unreleased]: https://github.com/rlanvin/php-rrule/compare/v2.4.0...HEAD
[2.4.0]: https://github.com/rlanvin/php-rrule/compare/v2.3.2...v2.4.0
[2.3.2]: https://github.com/rlanvin/php-rrule/compare/v2.3.1...v2.3.2
[2.3.1]: https://github.com/rlanvin/php-rrule/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/rlanvin/php-rrule/compare/v2.2.2...v2.3.0
[2.2.2]: https://github.com/rlanvin/php-rrule/compare/v2.2.1...v2.2.2
[2.2.1]: https://github.com/rlanvin/php-rrule/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/rlanvin/php-rrule/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/rlanvin/php-rrule/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/rlanvin/php-rrule/compare/v2.0.0-rc1...v2.0.0
[2.0.0-rc1]: https://github.com/rlanvin/php-rrule/compare/v1.6.3...v2.0.0-rc1
[1.6.3]: https://github.com/rlanvin/php-rrule/compare/v1.6.2...v1.6.3
[1.6.2]: https://github.com/rlanvin/php-rrule/compare/v1.6.1...v1.6.2
[1.6.1]: https://github.com/rlanvin/php-rrule/compare/v1.6.0...v1.6.1
[1.6.0]: https://github.com/rlanvin/php-rrule/compare/v1.5.1...v1.6.0
[1.5.1]: https://github.com/rlanvin/php-rrule/compare/v1.5.0...v1.5.1
[1.5.0]: https://github.com/rlanvin/php-rrule/compare/v1.4.2...v1.5.0
[1.4.2]: https://github.com/rlanvin/php-rrule/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/rlanvin/php-rrule/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/rlanvin/php-rrule/compare/v1.3.1...v1.4.0
[1.3.1]: https://github.com/rlanvin/php-rrule/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/rlanvin/php-rrule/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/rlanvin/php-rrule/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/rlanvin/php-rrule/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/rlanvin/php-rrule/compare/v1.0.0...v1.0.1
