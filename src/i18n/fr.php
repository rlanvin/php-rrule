<?php

/**
 * Translation file for French language.
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
		'1' => 'tous les ans',
		'2' => 'un an sur deux',
		'else' => 'tous les %{interval} ans'
	),
	'monthly' => array(
		'1' => 'tous les mois',
		'2' => 'un mois sur deux',
		'else' => 'tous les %{interval} mois'
	),
	'weekly' => array(
		'1' => 'toutes les semaines',
		'2' => 'une semaine sur deux',
		'else' => 'toutes les %{interval} semaines'
	),
	'daily' => array(
		'1' => 'tous les jours',
		'2' => 'un jour sur deux',
		'else' => 'tous les %{interval} jours'
	),
	'hourly' => array(
		'1' => 'toutes les heures',
		'else' => 'toutes les %{interval} heures'
	),
	'minutely' => array(
		'1' => 'toutes les minutes',
		'else' => 'toutes les %{interval} minutes'
	),
	'secondly' => array(
		'1' => 'toutes les secondes',
		'else' => 'toutes les %{interval} secondes'
	),
	'dtstart' => ', à partir du %{date}',
	'infinite' => ', indéfiniment',
	'until' => ', jusqu\'au %{date}',
	'count' => array(
		'1' => ', une fois',
		'else' => ', %{count} fois'
	),
	'and' => 'et',
	'x_of_the_y' => array(
		'yearly' => '%{x} de l\'année', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} du mois',
	),
	'bymonth' => ' en %{months}',
	'months' => array(
		1 => 'janvier',
		2 => 'février',
		3 => 'mars',
		4 => 'avril',
		5 => 'mai',
		6 => 'juin',
		7 => 'juillet',
		8 => 'août',
		9 => 'septembre',
		10 => 'octobre',
		11 => 'november',
		12 => 'décembre',
	),
	'byweekday' => ' le %{weekdays}',
	'weekdays' => array(
		1 => 'lundi',
		2 => 'mardi',
		3 => 'mercredi',
		4 => 'jeudi',
		5 => 'vendredi',
		6 => 'samedi',
		7 => 'dimanche',
	),
	'nth_weekday' => array(
		'1' => 'le 1er %{weekday}', // e.g. the first Monday
		'else' => 'le %{n}e %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'le dernier %{weekday}', // e.g. the last Monday
		'-2' => 'l\'avant-dernier %{weekday}',
		'-3' => 'l\'antépénultième %{weekday}',
		'else' => 'le %{n}e %{weekday} en partant de la fin'
	),
	'byweekno' => array(
		'1' => ' la semaine %{weeks}',
		'else' => ' les semaines %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => array(
		'1' => ' %{monthdays}',
		'else' => ' %{monthdays}'
	),
	'nth_monthday' => array(
		'1' => 'le 1er',
		'else' => 'le %{n}'
	),
	'-nth_monthday' => array(
		'-1' => 'le dernier jour',
		'-2' => 'l\'avant-dernier jour',
		'-3' => 'l\'antépénultième jour',
		'else' => 'le %{n}e jour en partant de la fin'
	),
	'byyearday' => array(
		'1' => ' le %{yeardays} jour',
		'else' => ' les %{yeardays} jours'
	),
	'nth_yearday' => array(
		'1' => '1er',
		'else' => '%{n}e'
	),
	'-nth_yearday' => array(
		'-1' => 'dernier',
		'-2' => 'avant-dernier',
		'-3' => 'antépénultième',
		'else' => '%{n}e en partant de la fin'
	),
	'byhour' => array(
		'1' => ' à %{hours}',
		'else' => ' à %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' à %{minutes}',
		'else' => ' à %{minutes}'
	),
	'nth_minute' => '%{n}min',
	'bysecond' => array(
		'1' => ' à %{seconds}',
		'else' => ' à %{seconds}'
	),
	'nth_second' => '%{n}sec',
	'bysetpos' => array(
		'1' => ', mais seulement %{setpos} occurrence',
		'else' => ', mais seulement %{setpos} occurrence'
	),
	'nth_setpos' => array(
		'1' => 'la 1re',
		'else' => 'la %{n}e'
	),
	'-nth_setpos' => array(
		'-1' => 'la dernière',
		'-2' => 'l\'avant-dernière',
		'-3' => 'l\'antépénultième',
		'else' => 'la %{n}e en partant de la fin'
	)
);