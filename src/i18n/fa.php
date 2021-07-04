<?php

/**
 * Translation file for Persian (Farsi) language.
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
		'1' => 'سالانه',
		'else' => 'هر %{interval} سال'
	),
	'monthly' => array(
		'1' => 'ماهانه',
		'else' => 'هر %{interval} ماه'
	),
	'weekly' => array(
		'1' => 'هفتگی',
		'2' => 'هر هفته دیگر',
		'else' => 'هر %{interval} هفته'
	),
	'daily' => array(
		'1' => 'روزانه',
		'2' => 'هر روز دیگر',
		'else' => 'هر %{interval} روز'
	),
	'hourly' => array(
		'1' => 'ساعتی',
		'else' => 'هر %{interval} ساعت'
	),
	'minutely' => array(
		'1' => 'دقیقه ای',
		'else' => 'هر %{interval} دقیقه'
	),
	'secondly' => array(
		'1' => 'ثانیه ای',
		'else' => 'هر %{interval} ثانیه'
	),
	'dtstart' => ', از %{date}',
	'infinite' => ', همیشه',
	'until' => ', تا %{date}',
	'count' => array(
		'1' => ', یکبار',
		'else' => ', %{count} بار'
	),
	'and' => 'و',
	'x_of_the_y' => array(
		'yearly' => '%{x} از سال', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} از ماه',
	),
	'bymonth' => ' در %{months}',
	'months' => array(
		1 => 'January',
		2 => 'February',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	),
	'byweekday' => ' در %{weekdays}',
	'weekdays' => array(
		1 => 'دوشنبه',
		2 => 'سه شنبه',
		3 => 'چهارشنبه',
		4 => 'پنج شنبه',
		5 => 'جمعه',
		6 => 'شنبه',
		7 => 'یکشنبه',
	),
	'nth_weekday' => array(
		'1' => 'اولین %{weekday}', // e.g. the first Monday
		'2' => 'دومین %{weekday}',
		'3' => 'سومین %{weekday}',
		'else' => '%{n}اٌمین %{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => 'آخرین %{weekday}', // e.g. the last Monday
		'-2' => 'دو روز مونده به %{weekday}',
		'-3' => 'سه زور مونده به %{weekday}',
		'else' => '%{n} روز مونده به %{weekday}'
	),
	'byweekno' => array(
		'1' => ' در %{weeks}',
		'else' => ' در شماره هفته %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' در %{monthdays}',
	'nth_monthday' => array(
		'1' => 'اولین',
		'2' => 'دومین',
		'3' => 'سومین',
		'21' => '۲۱اْمین',
		'22' => '۲۲اْمین',
		'23' => '۲۳اْمین',
		'31' => '۳۱اْمین',
		'else' => 'در %{n}اْمین'
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
		'1' => ' در %{yeardays} روز',
		'else' => ' در %{yeardays} روز'
	),
	'nth_yearday' => array(
		'1' => 'اولین',
		'2' => 'دومین',
		'3' => 'سومین',
		'else' => '%{n}اٌمین'
	),
	'-nth_yearday' => array(
		'-1' => 'آخرین',
		'-2' => 'دو روز مونده',
		'-3' => 'سه روز مونده',
		'else' => '%{n}اٌمین مونده به آخر'
	),
	'byhour' => array(
		'1' => ' ساعت %{hours}',
		'else' => ' ساعت %{hours}'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'1' => ':%{minutes}',
		'else' => ' در %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' at second %{seconds}',
		'else' => ' at seconds %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', but only %{setpos} instance of this set',
	'nth_setpos' => array(
		'1' => 'اولین',
		'2' => 'دومین',
		'3' => 'سومین',
		'else' => '%{n}اٌمین'
	),
	'-nth_setpos' => array(
		'-1' => 'آخرین',
		'-2' => 'دو روز مونده',
		'-3' => 'سه روز مونده',
		'else' => '%{n}اٌمین مونده به آخر'
	)
);
