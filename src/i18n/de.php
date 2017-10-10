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
	'infinite' => ', für immer',
	'until' => ', bis zum %{date}',
	'count' => array(
		'1' => ', einmalig',
		'else' => ', %{count} Mal'
	),
	'and' => 'und',
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
	'byweekday' => ' %{weekdays}',
	'weekdays' => array(
		1 => 'Montags',
		2 => 'Dienstags',
		3 => 'Mittwochs',
		4 => 'Donnerstags',
		5 => 'Freitags',
		6 => 'Samstags',
		7 => 'Sonntags',
	),
	'nth_weekday' => array(
		'1' => 'der erste %{weekday}', // e.g. the first Monday
		'2' => 'der zweite %{weekday}',
		'3' => 'der dritte %{weekday}',
		'else' => 'der %{n}. %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'der letzte %{weekday}', // e.g. the last Monday
		'-2' => 'der vorletzte %{weekday}',
		'else' => 'der %{n}. letzte %{weekday}'
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
		'-1' => 'der letzte',
		'-2' => 'der vorletzte',
		'else' => 'der %{n}. letzte'
	),
	'byhour' => array(
		'1' => ' um %{hours} Uhr',
		'else' => ' um %{hours} Uhr'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'1' => ' und %{minutes} Minute',
		'else' => ' und %{minutes} Minuten'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' und %{seconds} Sekunde',
		'else' => ' und %{seconds} Sekunden'
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