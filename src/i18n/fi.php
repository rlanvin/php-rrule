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
		'else' => '%{interval} kuukauden välein'
	),
	'weekly' => array(
		'1' => 'joka viikko',
		'2' => 'joka toinen viikko',
		'else' => '%{interval} viikon välein'
	),
	'daily' => array(
		'1' => 'joka päivä',
		'2' => 'joka toinen päivä',
		'else' => '%{interval} päivän välein'
	),
	'hourly' => array(
		'1' => 'joka tunti',
		'else' => 'joka %{interval} tunti'
	),
	'minutely' => array(
		'1' => 'joka minuutti',
		'else' => 'joka %{interval} minuutti'
	),
	'secondly' => array(
		'1' => 'joka sekunti',
		'else' => 'joka %{interval} sekunti'
	),
	'dtstart' => ', alkaen %{date}',
	'timeofday' => ' klo %{date}',
	'startingtimeofday' => ' alkaen %{date}',
	'infinite' => ', jatkuvasti',
	'until' => ', %{date} asti',
	'count' => array(
		'1' => ', kerran',
		'else' => ', %{count} kertaa'
	),
	'and' => 'ja ',
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
		'else' => '%{n}. %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'viimeinen %{weekday}', // e.g. the last Monday
		'-2' => 'toiseksi viimeinen %{weekday}',
		'else' => '%{n}:ksi viimeinen %{weekday}'
	),
	'byweekno' => array(
		'1' => ' viikkona %{weeks}',
		'else' => ' viikkoina %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' %{monthdays} päivä',
	'nth_monthday' => array(
		'else' => '%{n}.'
	),
	'-nth_monthday' => array(
		'-1' => 'viimeinen',
		'else' => '%{n}:ksi viimeinen'
	),
	'byyearday' => array(
		'else' => ' %{yeardays} päivä'
	),
	'nth_yearday' => array(
		'else' => '%{n}.'
	),
	'-nth_yearday' => array(
		'-1' => 'viimeinen',
		'else' => '%{n}:ksi viimeinen'
	),
	'byhour' => array(
		'else' => ' klo. %{hours}'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'else' => ', minuutteina %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'else' => ', sekunteina %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', mutta vain %{setpos} tapaus edellä mainituista',
	'nth_setpos' => array(
		'else' => '%{n}.'
	),
	'-nth_setpos' => array(
		'-1' => 'viimeinen',
		'else' => '%{n}:ksi viimeinen'
	)
);
