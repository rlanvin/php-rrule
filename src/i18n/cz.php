<?php

/**
 * Translation file for Czech language.
 *
 * Most strings can be an array, with a value as the key. The system will
 * pick the translation corresponding to the key. The key "else" will be picked
 * if no matching value is found. This is useful for plurals.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Jakub Kluvánek <jakub@kluvanek.dev>
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'ročně',
    '2' => 'každé %{interval} roky',
    '3' => 'každé %{interval} roky',
    '4' => 'každé %{interval} roky',
		'else' => 'každých %{interval} let'
	),
	'monthly' => array(
		'1' => 'měsíčně',
    '2' => 'každé %{interval} měsíce',
    '3' => 'každé %{interval} měsíce',
    '4' => 'každé %{interval} měsíce',
		'else' => 'každých %{interval} měsíců'
	),
	'weekly' => array(
		'1' => 'týdně',
    '2' => 'každé %{interval} týdny',
    '3' => 'každé %{interval} týdny',
    '4' => 'každé %{interval} týdny',
		'else' => 'každých %{interval} týdnů'
	),
	'daily' => array(
		'1' => 'denně',
		'2' => 'každé %{interval} dny',
    '3' => 'každé %{interval} dny',
    '4' => 'každé %{interval} dny',
    '5' => 'každé %{interval} dny',
		'else' => 'každých %{interval} dnů'
	),
	'hourly' => array(
		'1' => 'každou hodinu',
    '2' => 'každé %{interval} hodiny',
    '3' => 'každé %{interval} hodiny',
    '4' => 'každé %{interval} hodiny',
		'else' => 'každých %{interval} hodin'
	),
	'minutely' => array(
		'1' => 'každou minutu',
    '2' => 'každé %{interval} minuty',
    '3' => 'každé %{interval} minuty',
    '4' => 'každé %{interval} minuty',
		'else' => 'každých %{interval} minut'
	),
	'secondly' => array(
		'1' => 'každou sekundu',
    '2' => 'každé %{interval} sekundy',
    '3' => 'každé %{interval} sekundy',
    '4' => 'každé %{interval} sekundy',
		'else' => 'každých %{interval} sekund'
	),
	'dtstart' => ', počínaje %{date}',
	'timeofday' => ' v %{date}',
	'startingtimeofday' => ' začínající v %{date}',
	'infinite' => ', navždy',
	'until' => ', do %{date}',
	'count' => array(
		'1' => ', jednou',
		'else' => ', %{count}x'
	),
	'and' => 'a ',
	'x_of_the_y' => array(
		'yearly' => '%{x} roku', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} měsíce'
	),
	'bymonth' => ' v %{months}',
	'months' => array(
		1 => 'leden',
		2 => 'únor',
		3 => 'březen',
		4 => 'duben',
		5 => 'květen',
		6 => 'červen',
		7 => 'červenec',
		8 => 'srpen',
		9 => 'září',
		10 => 'říjen',
		11 => 'listopad',
		12 => 'prosinec'
	),
	'byweekday' => ' v %{weekdays}',
	'weekdays' => array(
		1 => 'pondělí',
		2 => 'úterý',
		3 => 'středa',
		4 => 'čtvrtek',
		5 => 'pátek',
		6 => 'sobota',
		7 => 'neděle'
	),
	'nth_weekday' => array(
		'1' => 'první %{weekday}', // e.g. the first Monday
		'2' => 'druhé %{weekday}',
		'3' => 'třetí %{weekday}',
		'else' => '%{n}. %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'poslední %{weekday}', // e.g. the last Monday
		'-2' => 'předposlední %{weekday}',
		'else' => '%{n}. od konce %{weekday}'
	),
	'byweekno' => array(
		'1' => ' v týdnu %{weeks}',
		'else' => ' v týdnech č.%{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' ve dnech %{monthdays}',
	'nth_monthday' => array(
		'else' => '%{n}.'
	),
	'-nth_monthday' => array(
		'-1' => 'poslední',
		'-2' => 'předposlední',
		'else' => '%{n}. od konce'
	),
	'byyearday' => array(
    '1' => ' ve dnu %{yeardays}',
		'else' => ' ve dnech %{yeardays}'
	),
	'nth_yearday' => array(
		'1' => 'první',
		'2' => 'druhý',
		'3' => 'třetí',
		'else' => '%{n}.'
	),
	'-nth_yearday' => array(
		'-1' => 'poslední',
		'-2' => 'předposlední',
		'else' => '%{n}. od konce'
	),
	'byhour' => array(
    '1' => ' v hodině %{hours}',
		'else' => ' v hodiny %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
    '1' => ' v minutu %{minutes}',
		'else' => ' v minuty %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' v sekundě %{seconds}',
		'else' => ' v sekundy %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', ale pouze %{setpos} instance sady',
	'nth_setpos' => array(
		'1' => 'první',
		'2' => 'druhá',
		'3' => 'třetí',
		'else' => ' %{n}.'
	),
	'-nth_setpos' => array(
		'-1' => 'poslední',
		'-2' => 'předposlední',
		'else' => '%{n}. od konce'
	)
);
