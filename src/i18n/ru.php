<?php

/**
 * Translation file for Russian language.
 *
 * Most strings can be an array, with a value as the key. The system will
 * pick the translation corresponding to the key. The key "else" will be picked
 * if no matching value is found. This is useful for plurals.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @autor Kirill Devope <main@devope.ru>
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'ежегодно', // or "каждый год"
		'2' => 'каждые %{interval} года',
		'3' => 'каждые %{interval} года',
		'4' => 'каждые %{interval} года',
		'101' => 'каждые %{interval} год', // 201, 301, etc.
		'102' => 'каждые %{interval} года', // 202, 302, etc.
		'103' => 'каждые %{interval} года', // 203, 303, etc.
		'104' => 'каждые %{interval} года', // 204, 304, etc.
		'else' => 'каждые %{interval} лет'
	),
	'monthly' => array(
		'1' => 'ежемесячно', // or "каждый месяц"
		'2' => 'каждые %{interval} месяца',
		'3' => 'каждые %{interval} месяца',
		'4' => 'каждые %{interval} месяца', // 5...20
		'21' => 'каждый %{interval} месяц', // 201, 301, etc.
		'22' => 'каждые %{interval} месяца', // 202, 302, etc.
		'23' => 'каждые %{interval} месяца', // 203, 303, etc.
		'24' => 'каждые %{interval} месяца', // 204, 304, etc.
		'else' => 'каждые %{interval} месяцев'
	),
	'weekly' => array(
		'1' => 'еженедельно', // or "каждую неделю"
		'2' => 'каждые %{interval} недели',
		'3' => 'каждые %{interval} недели',
		'4' => 'каждые %{interval} недели',
		'else' => 'каждые %{interval} недель' // 5...∞
	),
	'daily' => array(
		'1' => 'ежедневно',
		'2' => 'каждый %{interval}-й день',
		'3' => 'каждый %{interval}-й день',
		'4' => 'каждый %{interval}-й день',
		// TODO: 101...
		'else' => 'каждые %{interval} дней' // 5...100
	),
	'hourly' => array(
		'1' => 'ежечасно',
		'2' => 'каждые %{interval} часа',
		'3' => 'каждые %{interval} часа',
		'4' => 'каждые %{interval} часа',
		'else' => 'каждые %{interval} часов'
	),
	'minutely' => array(
		'1' => 'ежеминутно',
		'else' => 'каждые %{interval} минут'
	),
	'secondly' => array(
		'1' => 'ежесекундно',
		'else' => 'каждые %{interval} секунд'
	),
	'dtstart' => ', начиная с %{date}',
	'timeofday' => ' в %{date}',
	'startingtimeofday' => ' начиная в %{date}',
	'infinite' => ', всегда',
	'until' => ', до %{date}',
	'count' => array(
		'1' => ', один раз',
		'else' => ', %{count} раз'
	),
	'and' => 'и ',
	'x_of_the_y' => array(
		'yearly' => '%{x} года', // например, первый понедельник года или первый день года
		'monthly' => '%{x} месяца',
	),
	'bymonth' => ' в %{months}',
	'months' => array(
		1 => 'январе',
		2 => 'феврале',
		3 => 'марте',
		4 => 'апреле',
		5 => 'мае',
		6 => 'июне',
		7 => 'июле',
		8 => 'август',
		9 => 'сентябре',
		10 => 'октябре',
		11 => 'ноябре',
		12 => 'декабре',
	),
	'byweekday' => ' в %{weekdays}',
	'weekdays' => array(
		1 => 'понедельник',
		2 => 'вторник',
		3 => 'среда',
		4 => 'четверг',
		5 => 'пятница',
		6 => 'суббота',
		7 => 'воскресенье',
	),
	'nth_weekday' => array(
		'1' => 'первый %{weekday}', // например, первый понедельник
		'2' => 'второй %{weekday}',
		'3' => 'третий %{weekday}',
		'else' => '%{n}-й %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'последний %{weekday}', // например, последний понедельник
		'-2' => 'предпоследний %{weekday}',
		'-3' => 'предпредпоследний %{weekday}',
		'else' => '%{n}-й до последнего %{weekday}'
	),
	'byweekno' => array(
		'1' => ' на %{weeks} неделе',
		'else' => ' на %{weeks} неделях'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' в %{monthdays}',
	'nth_monthday' => array(
		'1' => '1-го',
		'2' => '2-го',
		'3' => '3-го',
		'21' => '21-го',
		'22' => '22-го',
		'23' => '23-го',
		'31' => '31-го',
		'else' => '%{n}-го'
	),
	'-nth_monthday' => array(
		'-1' => 'последний день',
		'-2' => 'предпоследний день',
		'-3' => 'предпредпоследний день',
		'-21' => '21-й до последнего дня',
		'-22' => '22-й до последнего дня',
		'-23' => '23-й до последнего дня',
		'-31' => '31-й до последнего дня',
		'else' => '%{n}-й до последнего дня'
	),
	'byyearday' => array(
		'1' => ' на %{yeardays} дне',
		'else' => ' на %{yeardays} днях'
	),
	'nth_yearday' => array(
		'1' => 'первый',
		'2' => 'второй',
		'3' => 'третий',
		'else' => '%{n}-й'
	),
	'-nth_yearday' => array(
		'-1' => 'последний',
		'-2' => 'предпоследний',
		'-3' => 'предпредпоследний',
		'else' => '%{n}-й до последнего'
	),
	'byhour' => array(
		'1' => ' в %{hours} часов',
		'else' => ' в %{hours} часов'
	),
	'nth_hour' => '%{n}ч',
	'byminute' => array(
		'1' => ' на %{minutes} минуте',
		'else' => ' на %{minutes} минутах'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' на %{seconds} секунде',
		'else' => ' на %{seconds} секундах'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', но только %{setpos} экземпляр этого набора',
	'nth_setpos' => array(
		'1' => 'первый',
		'2' => 'второй',
		'3' => 'третий',
		'else' => '%{n}-й'
	),
	'-nth_setpos' => array(
		'-1' => 'последний',
		'-2' => 'предпоследний',
		'-3' => 'предпредпоследний',
		'else' => '%{n}-й до последнего'
	)
);