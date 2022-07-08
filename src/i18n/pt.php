<?php

/**
 * Translation file for Portuguese language.
 *
 * @author Felippe Roberto Bayestorff Duarte <felippeduarte@gmail.com>
 * @link https://github.com/rlanvin/php-rrule
 */

return array(
	'yearly' => array(
		'1' => 'anual',
		'else' => 'cada %{interval} anos' // cada 8 anos
	),
	'monthly' => array(
		'1' => 'mensal',
		'else' => 'cada %{interval} meses' //cada 8 meses
	),
	'weekly' => array(
		'1' => 'semanal',
		'2' => 'qualquer outra semana',
		'else' => 'cada %{interval} semanas' // cada 8 semanas
	),
	'daily' => array(
		'1' => 'diário',
		'2' => 'qualquer outro dia',
		'else' => 'cada %{interval} dias' // cada 8 dias
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
		'1' => 'cada segundo',
		'else' => 'cada %{interval} segundos'// cada 8 segundos
	),
	'dtstart' => ', começando de %{date}',
	'infinite' => ', para sempre',
	'until' => ', até %{date}',
	'count' => array(
		'1' => ', uma vez',
		'else' => ', %{count} vezes'
	),
	'and' => 'e ',
	'x_of_the_y' => array(
		'yearly' => '%{x} do ano', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} do mês',
	),
	'bymonth' => ' em %{months}',
	'months' => array(
		1 => 'Janeiro',
		2 => 'Fevereiro',
		3 => 'Março',
		4 => 'Abril',
		5 => 'Maio',
		6 => 'Junho',
		7 => 'Julho',
		8 => 'Agosto',
		9 => 'Setembro',
		10 => 'Outubro',
		11 => 'Novembro',
		12 => 'Dezembro',
	),
	'byweekday' => ' em %{weekdays}',
	'weekdays' => array(
		1 => 'Segunda-feira',
		2 => 'Terça-feira',
		3 => 'Quarta-feira',
		4 => 'Quinta-feira',
		5 => 'Sexta-feira',
		6 => 'Sábado',
		7 => 'Domingo',
	),
	'nth_weekday' => array(
		'1' => 'o(a) primero(a) %{weekday}', // e.g. the first Monday
		'2' => 'o(a) segundo(a) %{weekday}',
		'3' => 'o(a) terceiro(a) %{weekday}',
		'else' => 'o %{n}º %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'o(a) último(a) %{weekday}', // e.g. the last Monday
		'-2' => 'o(a) penúltimo(a) %{weekday}',
		'-3' => 'o(a) antepeúltimo(a) %{weekday}',
		'else' => 'o %{n}º até o último	%{weekday}'
	),
	'byweekno' => array(
		'1' => ' na semana %{weeks}',
		'else' => ' nas semanas # %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' no %{monthdays}',
	'nth_monthday' => array(
		'1' => 'o 1º',
		'2' => 'o 2º',
		'3' => 'o 3º',
		'21' => 'o 21º',
		'22' => 'o 22º',
		'23' => 'o 23º',
		'31' => 'o 31º',
		'else' => 'o %{n}º'
	),
	'-nth_monthday' => array(
		'-1' => 'o último dia',
		'-2' => 'o penúltimo dia',
		'-3' => 'o antepenúltimo dia',
		'-21' => 'o 21º até o último dia',
		'-22' => 'o 22º até o último dia',
		'-23' => 'o 23º até o último dia',
		'-31' => 'o 31º até o último dia',
		'else' => 'o %{n}º até o último dia'
	),
	'byyearday' => array(
		'1' => ' no %{yeardays} dia',
		'else' => ' nos %{yeardays} dias'
	),
	'nth_yearday' => array(
		'1' => 'o primero',
		'2' => 'o segundo',
		'3' => 'o tercero',
		'else' => 'o %{n}º'
	),
	'-nth_yearday' => array(
		'-1' => 'o último',
		'-2' => 'o penúltimo',
		'-3' => 'o antepenúltimo',
		'else' => 'o %{n}º até o último'
	),
	'byhour' => array(
		'1' => ' a %{hours}',
		'else' => ' a %{hours}'
	),
	'nth_hour' => '%{n}h',
	'byminute' => array(
		'1' => ' ao minuto %{minutes}',
		'else' => ' aos minutos %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' ao segundo %{seconds}',
		'else' => ' aos segundos %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', mas somente %{setpos} ocorrência deste conjunto',
	'nth_setpos' => array(
		'1' => 'o primero',
		'2' => 'o segundo',
		'3' => 'o terceiro',
		'else' => 'o %{n}º'
	),
	'-nth_setpos' => array(
		'-1' => 'o último',
		'-2' => 'o penúltimo',
		'-3' => 'o antepenúltimo',
		'else' => 'o %{n}º até o último'
	)
);
