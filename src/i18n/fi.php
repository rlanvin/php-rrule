<?php

/**
 * Translation file for Finnish language.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-rrule
 *
 * @see http://people.uta.fi/~km56049/finnish/timexp.html
 */
return array(
	'yearly' => array(
		'1' => 'joka vuosi',
		'2' => 'joka toinen vuosi',
		'else' => '%{interval} vuoden välein'
	),
	'monthly' => array(
		'1' => 'joka kuukausi',
		'2' => 'joka toinen kuukausi',
		'else' => '%{count} kuukauden välein'
	),
	'weekly' => array(
		'1' => 'joka viikko',
		'2' => 'joka toinen viikko',
		'else' => '%{interval} viikon välein'
	),
	'daily' => array(
		'1' => 'joka päivä',
		'2' => 'joka toinen päivä',
		'else' => '%{count} päivän välein'
	),
	'hourly' => array(
		'1' => 'hourly',
		'else' => 'every %{interval} hours'
	),
	'minutely' => array(
		'1' => 'minutely',
		'else' => 'every %{interval} minutes'
	),
	'secondly' => array(
		'1' => 'secondly',
		'else' => 'every %{interval} seconds'
	),
	'dtstart' => ', alkaen %{date}',
	'infinite' => ', ikuisesti',
	'until' => ', loppuu %{date}',
	'count' => array(
		'1' => ', kerran',
		'else' => ', %{count} kertaa'
	),
	'and' => 'ja',
	'x_of_the_y' => array(
		'yearly' => '%{x} vuodessa', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} kuukaudessa',
	),
	'bymonth' => ' %{months}',
	'months' => array(
		1 => 'tammikuussa',
		2 => 'helmikuussa',
		3 => 'maaliskuussa',
		4 => 'huhtikuussa',
		5 => 'toukokuussa',
		6 => 'kesäkuussa',
		7 => 'heinäkuussa',
		8 => 'elokuussa',
		9 => 'syyskuussa',
		10 => 'lokakuussa',
		11 => 'marraskuussa',
		12 => 'joulukuussa',
	),
	'byweekday' => ' %{weekdays}',
	'weekdays' => array(
		1 => 'maanantaina',
		2 => 'tiistaina',
		3 => 'keskiviikkona',
		4 => 'torstaina',
		5 => 'perjantaina',
		6 => 'lauantaina',
		7 => 'sunnuntaina',
	),
	'nth_weekday' => array(
		'1' => 'the first %{weekday}', // e.g. the first Monday
		'2' => 'the second %{weekday}',
		'3' => 'the third %{weekday}',
		'else' => 'the %{n}th %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'the last %{weekday}', // e.g. the last Monday
		'-2' => 'the penultimate %{weekday}',
		'-3' => 'the antepenultimate %{weekday}',
		'else' => 'the %{n}th to the last %{weekday}'
	),
	'byweekno' => array(
		'1' => ' on week %{weeks}',
		'else' => ' on weeks number %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' %{monthdays} päivinä',
	'nth_monthday' => array(
		'else' => '%{n}.'
	),
	'-nth_monthday' => array(
		'-1' => 'the last day',
		'-2' => 'the penultimate day',
		'-3' => 'the antepenultimate day',
		'-21' => 'the 21st to the last day',
		'-22' => 'the 22nd to the last day',
		'-23' => 'the 23rd to the last day',
		'-31' => 'the 31st to the last day',
		'else' => 'the %{n}th to the last day'
	),
	'byyearday' => array(
		'1' => ' on %{yeardays} day',
		'else' => ' on %{yeardays} days'
	),
	'nth_yearday' => array(
		'1' => 'the first',
		'2' => 'the second',
		'3' => 'the third',
		'else' => 'the %{n}th'
	),
	'-nth_yearday' => array(
		'-1' => 'the last',
		'-2' => 'the penultimate',
		'-3' => 'the antepenultimate',
		'else' => 'the %{n}th to the last'
	),
	'byhour' => array(
		'1' => ' at %{hours}',
		'else' => ' at %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' at minute %{minutes}',
		'else' => ' at minutes %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' at second %{seconds}',
		'else' => ' at seconds %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', but only %{setpos} instance of this set',
	'nth_setpos' => array(
		'1' => 'the first',
		'2' => 'the second',
		'3' => 'the third',
		'else' => 'the %{n}th'
	),
	'-nth_setpos' => array(
		'-1' => 'the last',
		'-2' => 'the penultimate',
		'-3' => 'the antepenultimate',
		'else' => 'the %{n}th to the last'
	)
);