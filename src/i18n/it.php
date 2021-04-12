<?php

/**
 * Translation file for Italian language.
 *
 * @author Manuele Grueff <shoresofnowhere@gmail.com>
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'annualmente',
		'else' => 'ogni %{interval} anni'
	),
	'monthly' => array(
		'1' => 'mensilmente',
		'else' => 'ogni %{interval} mesi'
	),
	'weekly' => array(
		'1' => 'settimanalmente',
		'2' => 'a settimane alterne',
		'else' => 'ogni %{interval} settimana'
	),
	'daily' => array(
		'1' => 'giornalmente',
		'2' => 'a giorni alterni',
		'else' => 'every %{interval} days'
	),
	'hourly' => array(
		'1' => 'ogni ora',
		'else' => 'ogni %{interval} ore'
	),
	'minutely' => array(
		'1' => 'ogni minuto',
		'else' => 'ogni %{interval} minuti'
	),
	'secondly' => array(
		'1' => 'ogni secondo',
		'else' => 'ogni %{interval} secondi'
	),
	'dtstart' => ', a partire dal %{date}',
	'infinite' => ', per sempre',
	'until' => ', fino al %{date}',
	'count' => array(
		'1' => ', una volta',
		'else' => ', %{count} volte'
	),
	'and' => 'e ',
	'x_of_the_y' => array(
		'yearly' => '%{x} dell\'anno', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} del mese',
	),
	'bymonth' => ' in %{months}',
	'months' => array(
		1 => 'Gennaio',
		2 => 'Febbraio',
		3 => 'Marzo',
		4 => 'Aprile',
		5 => 'Maggio',
		6 => 'Giugno',
		7 => 'Luglio',
		8 => 'Agosto',
		9 => 'Settembre',
		10 => 'Ottobre',
		11 => 'Novembre',
		12 => 'Dicembre',
	),
	'byweekday' => ' il %{weekdays}',
	'weekdays' => array(
		1 => 'Lunedì',
		2 => 'Martedì',
		3 => 'Mercoledì',
		4 => 'Giovedì',
		5 => 'Venerdì',
		6 => 'Sabato',
		7 => 'Domenica',
	),
	'nth_weekday' => array(
		'1' => 'il primo %{weekday}', // e.g. the first Monday
		'2' => 'il secondo %{weekday}',
		'3' => 'il terzo %{weekday}',
		'else' => 'il %{n}o %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'l\'ultimo %{weekday}', // e.g. the last Monday
		'-2' => 'il penultimo %{weekday}',
		'-3' => 'il secondultimo %{weekday}',
		'else' => '%{n} %{weekday} prima dell\'ultimo %{weekday}'
	),
	'byweekno' => array(
		'1' => ' nella %{weeks} settimana',
		'else' => ' nelle settimane %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' il %{monthdays}',
	'nth_monthday' => array(
		'1' => 'il primo',
		'2' => 'il secondo',
		'3' => 'il terzo',
		'21' => 'il ventunesimo',
		'22' => 'il ventiduesimo',
		'23' => 'il ventitresimo',
		'31' => 'il trentunesimo',
		'else' => 'il %{n}'
	),
	'-nth_monthday' => array(
		'-1' => 'l\'ultimo giorno',
		'-2' => 'il penultimo giorno',
		'-3' => 'il secondultimo giorno',
		'else' => 'a %{n} giorni dalla fine mese'
	),
	'byyearday' => array(
		'1' => ' on %{yeardays} day',
		'else' => ' on %{yeardays} days'
	),
	'nth_yearday' => array(
		'1' => 'il primo',
		'2' => 'il secondo',
		'3' => 'il terzo',
		'else' => 'il %{n}o'
	),
	'-nth_yearday' => array(
		'-1' => 'l\'ultimo giorno',
		'-2' => 'il penultimo giorno',
		'-3' => 'il secondultimo giorno',
		'else' => 'a %{n} giorni dalla fine anno'
	),
	'byhour' => array(
		'1' => ' alle %{hours}',
		'else' => ' alle %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' al minuto %{minutes}',
		'else' => ' ai minuti %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' al secondo %{seconds}',
		'else' => ' ai secondi %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', ma solo la %{setpos} occorrenza',
	'nth_setpos' => array(
		'1' => 'la prima',
		'2' => 'la seconda',
		'3' => 'la terza',
		'else' => 'la %{n} occorrenza'
	),
	'-nth_setpos' => array(
		'-1' => 'la ultima',
		'-2' => 'la penultima',
		'-3' => 'la secondultima',
		'else' => 'a %{n} occorrenze dall\'ultima'
	)
);
?>
