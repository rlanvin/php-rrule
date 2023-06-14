<?php

/**
 * Translation file for German language.
 *
 * Most strings can be an array, with a value as the key. The system will
 * pick the translation corresponding to the key. The key "else" will be picked
 * if no matching value is found. This is useful for plurals.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'Jährlich',
		'else' => 'Alle %{interval} Jahre'
	),
	'monthly' => array(
		'1' => 'Monatlich',
		'else' => 'Alle %{interval} Monate'
	),
	'weekly' => array(
		'1' => 'Wöchentlich',
		'else' => 'Alle %{interval} Wochen'
	),
	'daily' => array(
		'1' => 'Täglich',
		'else' => 'Alle %{interval} Tage'
	),
	'hourly' => array(
		'1' => 'Stündlich',
		'else' => 'Alle %{interval} Stunden'
	),
	'minutely' => array(
		'1' => 'Minütlich',
		'else' => 'Alle %{interval} Minuten'
	),
	'secondly' => array(
		'1' => 'Sekündlich',
		'else' => 'Alle %{interval} Sekunden'
	),
	'dtstart' => ', ab dem %{date}',
	'timeofday' => ' um %{date}',
	'startingtimeofday' => ' ab %{date}',
	'infinite' => ', für immer',
	'until' => ', bis zum %{date}',
	'count' => array(
		'1' => ', einmalig',
		'else' => ', %{count} Mal insgesamt'
	),
	'and' => 'und ',
	'x_of_the_y' => array(
		'yearly' => '%{x} des Jahres', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} des Monats',
	),
	'bymonth' => ' im %{months}',
	'months' => array(
		1 => 'Januar',
		2 => 'Februar',
		3 => 'März',
		4 => 'April',
		5 => 'Mai',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'August',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Dezember',
	),
	'byweekday' => ' am %{weekdays}',
	'weekdays' => array(
		1 => 'Montag',
		2 => 'Dienstag',
		3 => 'Mittwoch',
		4 => 'Donnerstag',
		5 => 'Freitag',
		6 => 'Samstag',
		7 => 'Sonntag',
	),
	'nth_weekday' => array(
		'1' => 'ersten %{weekday}', // e.g. the first Monday
		'2' => 'zweiten %{weekday}',
		'3' => 'dritten %{weekday}',
		'else' => '%{n}. %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'letzten %{weekday}', // e.g. the last Monday
		'-2' => 'vorletzten %{weekday}',
		'else' => ' %{n}. letzten %{weekday}'
	),
	'byweekno' => array(
		'1' => ' in Kalenderwoche %{weeks}',
		'else' => ' in Kalenderwoche %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' am %{monthdays}',
	'nth_monthday' => array(
        '1' => 'ersten Tag',
        'else' => '%{n}. Tag'
	),
	'-nth_monthday' => array(
		'-1' => 'letzten Tag',
		'else' => '%{n}. letzten Tag'
	),
	'byyearday' => array(
		'1' => ' am %{yeardays} Tag',
		'else' => ' am %{yeardays} Tag'
	),
	'nth_yearday' => array(
		'1' => 'ersten',
		'2' => 'zweiten',
		'3' => 'dritten',
		'else' => '%{n}.'
	),
	'-nth_yearday' => array(
		'-1' => 'letzten',
		'-2' => 'vorletzten',
		'else' => '%{n}. letzten'
	),
	'byhour' => array(
		'1' => ' zur %{hours} Stunde',
		'else' => ' zur %{hours} Stunde'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'1' => ' und %{minutes} Minute',
		'else' => ' und %{minutes} Minute'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' und %{seconds} Sekunde',
		'else' => ' und %{seconds} Sekunde'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', nur %{setpos} Auftreten',
	'nth_setpos' => array(
		'1' => 'das erste',
		'2' => 'das zweite',
		'3' => 'das dritte',
		'else' => 'das %{n}.'
	),
	'-nth_setpos' => array(
		'-1' => 'die letzte',
		'-2' => 'die vorletzte',
		'else' => 'die %{n}. letzte'
	)
);
