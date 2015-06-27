# RRULE for PHP

Lightweight and fast implementation of recurrence rules for PHP (RFC 5545), to easily work with recurring dates and events (such as in a calendar).
This library is heavily based on [python-dateutil](https://labix.org/python-dateutil).

[![Build Status](https://travis-ci.org/rlanvin/php-rrule.svg?branch=master)](https://travis-ci.org/rlanvin/php-rrule)

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
	echo $occurrence->format('D d M Y'),"\n";
}

// will output:
// Mon 01 Jun 2015
// Wed 01 Jul 2015
// Sat 01 Aug 2015
// Tue 01 Sep 2015
// Thu 01 Oct 2015
// Sun 01 Nov 2015
```

Complete doc is available in [the wiki](https://github.com/rlanvin/php-rrule/wiki).

## Requirements

- PHP >= 5.3

## Installation

This is still a work in progress, use at your own risk!
In particular, HOURLY, MINUTELY and SECONDLY frequencies are not implemented.

The recommended way is to install the lib [through Composer](http://getcomposer.org/).

Just add this to your `composer.json` file:

```JSON
{
    "require": {
        "rlanvin/php-rrule": "dev-master*"
    }
}
```

Then run `composer install` or `composer update`.

Now you can use the autoloader, and you will have access to the library:

```php
<?php
require 'vendor/autoload.php';
```

### Alternative method

Since it's a no-nonsense implementation, there is only one class.
So you can just download `src/RRule.php` and require it.

## Note

I started this library because I wasn't happy with the existing implementations
in PHP. The ones I tested were slow and/or had a very awkward/verbose API that
I didn't like to use. They were also all missing a generator/iterator, which I
think is key. So I thought it would be a good learning project to port the
python-dateutil rrule implementation into PHP.

The Python lib was a bit difficult to understand because the algorithms (very smart by the way),
are not commented and the variables are very opaque (I'm looking at
you `lno1wkst`). I tried to comment and explain as much of the algorithm as possible
in this PHP port, so feel free to check the code if you're interested.

The lib differs from the python version in various aspects, notably in the 
respect of the RFC. This version is strictier and will not accept many
non-compliant combinations of rule parts, that the python version otherwise accepts.
There are also some additional features in this version.

## Documentation

Complete doc is available in [the wiki](https://github.com/rlanvin/php-rrule/wiki).

## License

This library is released under the MIT License.
