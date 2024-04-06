<?php

/**
 * Translation file for Spanish language.
 *
 * @author Miguel Romero <mromero91.mr@gmail.com>
 * @link https://github.com/rlanvin/php-rrule
 */

return array(
	'yearly' => array(
		'1' => 'anual',
		'else' => 'cada %{interval} años' // cada 8 años
	),
	'monthly' => array(
		'1' => 'mensual',
		'else' => 'cada %{interval} meses' //cada 8 meses
	),
	'weekly' => array(
		'1' => 'semanal',
		'2' => 'semana por medio',
		'else' => 'cada %{interval} semanas' // cada 8 semanas
	),
	'daily' => array(
		'1' => 'diario',
		'2' => 'día por medio',
		'else' => 'cada %{interval} días' // cada 8 días
	),
	'hourly' => array(
		'1' => 'cada hora',
		'else' => 'cada %{interval} horas'// cada 8 horas
	),
	'minutely' => array(
		'1' => 'cada minuto',
		'else' => 'cada %{interval} minutos'// cada 8 minutos
	),
	'secondly' => array(
		'1' => 'segundo lugar',
		'else' => 'cada %{interval} segundos'// cada 8 segundos
	),
	'dtstart' => ', empezando desde %{date}',
	'timeofday' => ' a las %{date}',
	'startingtimeofday' => ' empezando desde %{date}',
	'infinite' => ', por siempre',
	'until' => ', hasta %{date}',
	'count' => array(
		'1' => ', una vez',
		'else' => ', %{count} veces'
	),
	'and' => 'y ',
	'x_of_the_y' => array(
		'yearly' => '%{x} del año', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} del mes',
	),
	'bymonth' => ' en %{months}',
	'months' => array(
		1 => 'Enero',
		2 => 'Febrero',
		3 => 'Marzo',
		4 => 'Abril',
		5 => 'Mayo',
		6 => 'Junio',
		7 => 'Julio',
		8 => 'Agosto',
		9 => 'Septiembre',
		10 => 'Octubre',
		11 => 'Noviembre',
		12 => 'Diciembre',
	),
	'byweekday' => ' en %{weekdays}',
	'weekdays' => array(
		1 => 'Lunes',
		2 => 'Martes',
		3 => 'Miércoles',
		4 => 'Jueves',
		5 => 'Viernes',
		6 => 'Sábado',
		7 => 'Domingo',
	),
	'nth_weekday' => array(
		'1' => 'El primer %{weekday}', // e.g. the first Monday
		'2' => 'El segundo %{weekday}',
		'3' => 'El tercero %{weekday}',
		'else' => 'El %{n}° %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'El último %{weekday}', // e.g. the last Monday
		'-2' => 'El penúltimo %{weekday}',
		'-3' => 'El antepenúltimo %{weekday}',
		'else' => 'El %{n}° hasta el último	 %{weekday}'
	),
	'byweekno' => array(
		'1' => ' en semana %{weeks}',
		'else' => ' en semana # %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' en %{monthdays}',
	'nth_monthday' => array(
		'1' => 'El 1°',
		'2' => 'El 2°',
		'3' => 'El 3°',
		'21' => 'El 21°',
		'22' => 'El 22°',
		'23' => 'El 23°',
		'31' => 'El 31°',
		'else' => 'El %{n}°'
	),
	'-nth_monthday' => array(
		'-1' => 'El último día',
		'-2' => 'El penúltimo día',
		'-3' => 'El antepenúltimo día',
		'-21' => 'El 21° hasta el último día',
		'-22' => 'El 22° hasta el último día',
		'-23' => 'El 23° hasta el último día',
		'-31' => 'El 31° hasta el último día',
		'else' => 'El %{n}° hasta el último día'
	),
	'byyearday' => array(
		'1' => ' en %{yeardays} día',
		'else' => ' en %{yeardays} días'
	),
	'nth_yearday' => array(
		'1' => 'El primero',
		'2' => 'El segundo',
		'3' => 'El tercero',
		'else' => 'El %{n}°'
	),
	'-nth_yearday' => array(
		'-1' => 'El último',
		'-2' => 'El penúltimo',
		'-3' => 'El antepenúltimo',
		'else' => 'El %{n}° hasta el último'
	),
	'byhour' => array(
		'1' => ' a %{hours}',
		'else' => ' a %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' a minutos %{minutes}',
		'else' => ' a minutos %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' a segundo %{seconds}',
		'else' => ' a segundo %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', pero solo %{setpos} instancia de este conjunto',
	'nth_setpos' => array(
		'1' => 'El primer',
		'2' => 'El segundo',
		'3' => 'El tercero',
		'else' => 'El %{n}°'
	),
	'-nth_setpos' => array(
		'-1' => 'El último',
		'-2' => 'El penúltimo',
		'-3' => 'El antepenúltimo',
		'else' => 'El %{n}° hasta el último'
	)
);
