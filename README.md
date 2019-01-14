# RRULE for PHP

Lightweight and fast implementation of recurrence rules for PHP (`RRULE` from RFC 5545), to easily calculate recurring/repeating dates and events (such as in a calendar).
This library started as a port of [python-dateutil](https://labix.org/python-dateutil).

[![Build Status](https://travis-ci.org/rlanvin/php-rrule.svg?branch=master)](https://travis-ci.org/rlanvin/php-rrule)
[![Latest Stable Version](https://poser.pugx.org/rlanvin/php-rrule/v/stable)](https://packagist.org/packages/rlanvin/php-rrule)
[![Total Downloads](https://poser.pugx.org/rlanvin/php-rrule/downloads)](https://packagist.org/packages/rlanvin/php-rrule)

## Basic example

```php
use RRule\RRule;

$rrule = new RRule([
	'FREQ' => 'MONTHLY',
	'INTERVAL' => 1,
	'DTSTART' => '2015-06-01',
	'COUNT' => 6
]);

foreach ( $rrule as $occurrence ) {
	echo $occurrence->format('D d M Y'),", ";
}
// Mon 01 Jun 2015, Wed 01 Jul 2015, Sat 01 Aug 2015, Tue 01 Sep 2015, Thu 01 Oct 2015, Sun 01 Nov 2015

echo $rrule->humanReadable(),"\n";
// monthly on the 1st of the month, starting from 01/06/2015, 6 times
```

Complete documentation and more examples are available in [the wiki](https://github.com/rlanvin/php-rrule/wiki).

## Requirements

- PHP >= 5.6
- [intl extension](http://php.net/manual/en/book.intl.php) is recommended for `humanReadable()` but not strictly required

## Installation

The recommended way is to install the lib [through Composer](http://getcomposer.org/).

Simply run `composer require rlanvin/php-rrule` for it to be automatically installed and included in your `composer.json`.

Now you can use the autoloader, and you will have access to the library:

```php
require 'vendor/autoload.php';
```

## Documentation

Complete documentation is available in [the wiki](https://github.com/rlanvin/php-rrule/wiki).

You will also find useful information in the [RFC 5545 section 3.3.10](https://tools.ietf.org/html/rfc5545#section-3.3.10).

## Contribution

Feel free to contribute! Just create a new issue or a new pull request.

The coding style is (mostly) PSR-2, but with tabs.

## Note

I started this library because I wasn't happy with the existing implementations
in PHP, so I thought it would be a good learning project to port the
python-dateutil rrule implementation into PHP.

The Python lib was a bit difficult to understand because the algorithms 
are not commented and the variables are very opaque (I'm looking at
you `lno1wkst`). I tried to comment and explain as much of the algorithm as possible
in this PHP port, so feel free to check the code if you're interested.

The lib differs from the python version in various aspects, notably in the 
respect of the RFC. This version is a bit strictier and will not accept many
non-compliant combinations of rule parts, that the python version otherwise accepts.
There are also some additional features in this version.

## License

This library is released under the MIT License.
