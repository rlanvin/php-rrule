<?php

/**
 * Translation file for Catalan language.
 * 
 * Most strings can be an array, with a value as the key. The system will
 * pick the translation corresponding to the key. The key "else" will be picked
 * if no matching value is found. This is useful for plurals.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Ramon Forns <ramonforns@gmail.com>
 * @link https://github.com/rlanvin/php-rrule
 */

return array(
	'yearly' => array(
		'1' => 'anual',
		'else' => 'cada %{interval} anys'
	),
	'monthly' => array(
		'1' => 'mensual',
		'else' => 'cada %{interval} mesos'
	),
	'weekly' => array(
		'1' => 'setmanal',
		'2' => 'quinzenal',
		'else' => 'cada %{interval} setmanes'
	),
	'daily' => array(
		'1' => 'diari',
		'else' => 'cada %{interval} dies'
	),
	'hourly' => array(
		'1' => 'cada hora',
		'else' => 'cada %{interval} hores'
	),
	'minutely' => array(
		'1' => 'cada minut',
		'else' => 'cada %{interval} minuts'
	),
	'secondly' => array(
		'1' => 'cada segon',
		'else' => 'cada %{interval} segons'
	),
	'dtstart' => ', començant el %{date}',
	'timeofday' => ' a les %{date}',
	'startingtimeofday' => ' començant des de %{date}',
	'infinite' => ', per sempre',
	'until' => ', fins %{date}',
	'count' => array(
		'1' => ', una vegada',
		'else' => ', %{count} vegades'
	),
	'and' => 'i ',
	'x_of_the_y' => array(
		'yearly' => '%{x} de l\'any', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} del mes',
	),
	'bymonth' => ' en %{months}',
	'months' => array(
		1 => 'gener',
		2 => 'febrer',
		3 => 'març',
		4 => 'abril',
		5 => 'maig',
		6 => 'juny',
		7 => 'juliol',
		8 => 'agost',
		9 => 'setembre',
		10 => 'octubre',
		11 => 'novembre',
		12 => 'desembre',
	),
	'byweekday' => ' els %{weekdays}',
	'weekdays' => array(
		1 => 'dilluns',
		2 => 'dimarts',
		3 => 'dimecres',
		4 => 'dijous',
		5 => 'divendres',
		6 => 'dissabte',
		7 => 'diumenge',
	),
	'nth_weekday' => array(
		'1' => 'el primer %{weekday}', // e.g. the first Monday
		'2' => 'el segon %{weekday}',
		'3' => 'el tercer %{weekday}',
		'4' => 'el quart %{weekday}',
		'else' => 'el %{n}è %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'l\'últim %{weekday}', // e.g. the last Monday
		'-2' => 'el penúltim %{weekday}',
		'-3' => 'l\'antepenúltim %{weekday}',
		'-4' => 'el 4t abans de l\'últim %{weekday}',
		'else' => 'el %{n}è abans de l\'últim %{weekday}'
	),
	'byweekno' => array(
		'1' => ' en la setmana %{weeks}',
		'else' => ' en les setmanes %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' en %{monthdays}',
	'nth_monthday' => array(
		'1' => 'el 1r',
		'2' => 'el 2n',
		'3' => 'el 3r',
		'4' => 'el 4t',
		'else' => 'el %{n}è'
	),
	'-nth_monthday' => array(
		'-1' => 'l\'últim dia',
		'-2' => 'el penúltim dia',
		'-3' => 'l\'antepenúltim dia',
		'-4' => 'el 4t abans de l\'últim dia',
		'else' => 'el %{n}è abans de l\'últim dia'
	),
	'byyearday' => array(
		'1' => ' en %{yeardays} dia',
		'else' => ' en %{yeardays} dies'
	),
	'nth_yearday' => array(
		'1' => 'el primer',
		'2' => 'el segon',
		'3' => 'el tercer',
		'4' => 'el quart',
		'else' => 'el %{n}è'
	),
	'-nth_yearday' => array(
		'-1' => 'l\'últim',
		'-2' => 'el penúltim',
		'-3' => 'l\'antepenúltim',
		'-4' => 'el %{n}t abans de l\'últim',
		'else' => 'el %{n}è abans de l\'últim'
	),
	'byhour' => array(
		'1' => ' a les %{hours}',
		'else' => ' a les %{hours}'
	),
	'nth_hour' => '%{n} h',
	'byminute' => array(
		'1' => ' al minut %{minutes}',
		'else' => ' als minuts %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' al segon %{seconds}',
		'else' => ' als segons %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', però només %{setpos} instància d\'aquest conjunt',
	'nth_setpos' => array(
		'1' => 'la primera',
		'2' => 'la segona',
		'3' => 'la tercera',
		'4' => 'la quarta',
		'else' => 'la %{n}a'
	),
	'-nth_setpos' => array(
		'-1' => 'l\'últim',
		'-2' => 'el penúltim',
		'-3' => 'l\'antepenúltim',
		'-4' => 'el %{n}t abans de l\'últim',
		'else' => 'el %{n}è abans de l\'últim'
	)
);
