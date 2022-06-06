<?php

/**
 * Translation file for Polish language.
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
 * @author Janusz Paszyński <j.paszynski@itchaos.pl>
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'co roku',
		'2' => 'co drugi rok',
		'3' => 'co trzeci rok',
		'else' => 'co %{interval} lat'
	),
	'monthly' => array(
		'1' => 'co miesiąc',
		'else' => 'co %{interval} miesięcy'
	),
	'weekly' => array(
		'1' => 'co tydzień',
		'2' => 'co drugi tydzień',
		'3' => 'co trzeci tydzień',
		'else' => 'co %{interval} tygodni'
	),
	'daily' => array(
		'1' => 'codziennie',
		'2' => 'co drugi dzień',
		'3' => 'co trzeci dzień',
		'else' => 'co %{interval} dni'
	),
	'hourly' => array(
		'1' => 'co godzinę',
		'2' => 'co drugą godzinę',
		'3' => 'co trzecią godzinę',
		'else' => 'co %{interval} godzin'
	),
	'minutely' => array(
		'1' => 'co minutę',
		'2' => 'co dwie minuty',
		'3' => 'co trzy minuty',
		'else' => 'co %{interval} minut'
	),
	'secondly' => array(
		'1' => 'co sekundę',
		'2' => 'co dwie sekundy',
		'3' => 'co trzy sekundy',
		'4' => 'co cztery sekundy',
		'else' => 'co %{interval} sekund'
	),
	'dtstart' => ', zaczynając od %{date}',
	'infinite' => ', zawsze',
	'until' => ', do daty %{date}',
	'count' => array(
		'1' => ', jeden raz',
		'else' => ', %{count} razy'
	),
	'and' => 'i ',
	'x_of_the_y' => array(
		'yearly' => '%{x} roku', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} miesiąca',
		'weekly' => '%{x} tygodnia',
	),
	'bymonth' => ' w %{months}',
	'months' => array(
		1 => 'Styczeń',
		2 => 'Luty',
		3 => 'Marzec',
		4 => 'Kwiecień',
		5 => 'Maj',
		6 => 'Czerwiec',
		7 => 'Lipiec',
		8 => 'Sierpień',
		9 => 'Wrzesień',
		10 => 'Październik',
		11 => 'Listopad',
		12 => 'Grudzień',
	),
	'byweekday' => ' w %{weekdays}',
	'weekdays' => array(
		1 => 'Poniedziałek',
		2 => 'Wtorek',
		3 => 'Środa',
		4 => 'Czwartek',
		5 => 'Piątek',
		6 => 'Sobota',
		7 => 'Niedziela',
	),
	'nth_weekday' => array(
		'1' => 'w pierwszy %{weekday}', // e.g. the first Monday
		'2' => 'w drugi %{weekday}',
		'3' => 'w trzeci %{weekday}',
		'else' => 'w %{n} %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'w ostatni %{weekday}', // e.g. the last Monday
		'-2' => 'w przedostatni %{weekday}',
		'else' => 'w %{n} od końca %{weekday}'
	),
	'byweekno' => array(
		'1' => ' w tygodniu %{weeks}',
		'else' => ' w tygodniach numer %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' w %{monthdays}',
	'nth_monthday' => array(
		'1' => 'pierwszy',
		'2' => 'drugi',
		'3' => 'trzeci',
		'else' => '%{n}.'
	),
	'-nth_monthday' => array(
		'-1' => 'ostatni',
		'-2' => 'przedostatni',
		'-3' => 'drugi od końca',
		'else' => '%{n}. od końca'
	),
	'byyearday' => array(
		'1' => ' pierwszego %{yeardays} dnia',
		'else' => ' w dniu %{yeardays}'
	),
	'nth_yearday' => array(
		'1' => 'pierwszy',
		'2' => 'drugi',
		'3' => 'trzeci',
		'else' => '%{n}.'
	),
	'-nth_yearday' => array(
		'-1' => 'ostatni',
		'-2' => 'przedostatni',
		'else' => '%{n}. od końca'
	),
	'byhour' => array(
		'1' => ' o %{hours}',
		'else' => ' o %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' w minucie %{minutes}',
		'else' => ' w minucie %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' w sekundzie %{seconds}',
		'else' => ' w sekundzie %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', tylko %{setpos} wystąpienie w zbiorze',
	'nth_setpos' => array(
		'1' => 'pierwszy',
		'2' => 'drugi',
		'3' => 'trzeci',
		'else' => 'the %{n}th'
	),
	'-nth_setpos' => array(
		'-1' => 'ostatni',
		'-2' => 'przedostatni',
		'else' => '%{n}. od końca'
	)
);
