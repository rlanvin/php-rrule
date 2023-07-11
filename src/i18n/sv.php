<?php

/**
 * Translation file for Swedish language.
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
		'1' => 'årligen',
		'2' => 'vartannat år',
		'else' => 'var %{interval}:e år'
	),
	'monthly' => array(
		'1' => 'månatligen',
		'2' => 'varannan månad',
		'else' => 'var %{interval}:e månad'
	),
	'weekly' => array(
		'1' => 'varje vecka',
		'2' => 'varannan vecka',
		'else' => 'var %{interval}:e vecka'
	),
	'daily' => array(
		'1' => 'dagligen',
		'2' => 'varannnan dag',
		'else' => 'var %{interval}:e dag'
	),
	'hourly' => array(
		'1' => 'varje timme',
		'2' => 'varannan timme',
		'else' => 'var %{interval}:e timme'
	),
	'minutely' => array(
		'1' => 'varje minut',
		'2' => 'varannan minut',
		'else' => 'var %{interval}:e minut'
	),
	'secondly' => array(
		'1' => 'varje sekund',
		'2' => 'varannan sekund',
		'else' => 'var %{interval}:e sekund'
	),
	'dtstart' => ', börjar %{date}',
	'timeofday' => ' kl %{date}',
	'startingtimeofday' => ' börjar %{date}',
	'infinite' => ', tills vidare',
	'until' => ', t.om %{date}',
	'count' => array(
		'1' => ', ett tillfälle',
		'else' => ', %{count} tillfällen'
	),
	'and' => 'och ',
	'x_of_the_y' => array(
		'yearly' => '%{x} på året', // ex. den första måndagen på året, eller den första dagen på året,  e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} i månaden',
	),
	'bymonth' => ' i %{months}',
	'months' => array(
		1 => 'Januari',
		2 => 'Februari',
		3 => 'Mars',
		4 => 'April',
		5 => 'Maj',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'Augusti',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'December',
	),
	'byweekday' => ' på %{weekdays}',
	'weekdays' => array(
		1 => 'Måndag',
		2 => 'Tisdag',
		3 => 'Onsdag',
		4 => 'Torsdag',
		5 => 'Fredag',
		6 => 'Lördag',
		7 => 'Söndag',
	),
	'nth_weekday' => array(
		'1' => 'den första %{weekday}en', // e.g. the first Monday
		'2' => 'den andra %{weekday}en',
		'else' => 'den %{n}:e %{weekday}en'
	),
	'-nth_weekday' => array(
		'-1' => 'den sista %{weekday}en', // e.g. the last Monday
		'-2' => 'näst sista %{weekday}en',
		'else' => 'den %{n}:e %{weekday}en från slutet'
	),
	'byweekno' => array(
		'1' => ' i vecka %{weeks}',
		'else' => ' i vecka %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => '%{monthdays}',
	'nth_monthday' => array(
		'1' => 'den 1:a',
		'2' => 'den 2:a',
		'3' => 'den 3:e',
		'21' => 'den 21:a',
		'22' => 'den 22:a',
		'23' => 'den 23:e',
		'31' => 'den 31:a',
		'else' => 'den %{n}:e'
	),
	'-nth_monthday' => array(
		'-1' => 'sista dagen',
		'-2' => 'den näst sista dagen',
		'else' => '%{n} dagar från sista dagen'
	),
	'byyearday' => array(
		'else' => ' på %{yeardays} dagen'
	),
	'nth_yearday' => array(
		'1' => 'den första',
		'2' => 'den andra',
		'3' => 'den tredje',
		'else' => 'den %{n}:e'
	),
	'-nth_yearday' => array(
		'-1' => 'sista',
		'-2' => 'den näst sista',
		'else' => '%{n} från sista'
	),
	'byhour' => array(
		'else' => ' vid %{hours}'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'else' => '.%{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'else' => '.%{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', men bara %{setpos} tillfället i serien',
	'nth_setpos' => array(
		'1' => 'den första',
		'2' => 'den andra',
		'3' => 'den tredje',
		'else' => 'den %{n}:e'
	),
	'-nth_setpos' => array(
		'-1' => 'den sista',
		'-2' => 'den näst sista',
		'else' => 'den %{n}:e sista'
	)
);
