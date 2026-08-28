<?php

/**
 * Translation file for English language.
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
		'1' => '每年',
		'else' => '每 %{interval} 年'
	),
	'monthly' => array(
		'1' => '每月',
		'else' => '每 %{interval} 月'
	),
	'weekly' => array(
		'1' => '每週',
		'2' => '隔週',
		'else' => '每 %{interval} 週'
	),
	'daily' => array(
		'1' => '每天',
		'2' => '隔天',
		'else' => '每 %{interval} 天'
	),
	'hourly' => array(
		'1' => '每小時',
		'else' => '每 %{interval} 小時'
	),
	'minutely' => array(
		'1' => '每分鐘',
		'else' => '每 %{interval} 分鐘'
	),
	'secondly' => array(
		'1' => '每秒鐘',
		'else' => '每 %{interval} 秒鐘'
	),
	'dtstart' => '，從 %{date} 開始',
	'infinite' => '，永遠不結束',
	'until' => '，直到 %{date} 結束',
	'count' => array(
		'1' => '，共 1 次',
		'else' => '，共 %{count} 次'
	),
	'and' => '和',
	'x_of_the_y' => array(
		'yearly' => '的 %{x}', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '的 %{x}',
	),
	'bymonth' => ' %{months}',
	'months' => array(
		1 => '一月',
		2 => '二月',
		3 => '三月',
		4 => '四月',
		5 => '五月',
		6 => '六月',
		7 => '七月',
		8 => '八月',
		9 => '九月',
		10 => '十月',
		11 => '十一月',
		12 => '十二月',
	),
	'byweekday' => ' %{weekdays}',
	'weekdays' => array(
		1 => '週一',
		2 => '週二',
		3 => '週三',
		4 => '週四',
		5 => '週五',
		6 => '週六',
		7 => '週日',
	),
	'nth_weekday' => array(
		'1' => '第 1 個%{weekday}', // e.g. the first Monday
		'else' => '第 %{n} 個%{weekday}'
	),
	'-nth_weekday' => array(
		'-1' => '最後一個%{weekday}', // e.g. the last Monday
		'else' => '倒數第 %{n} 個%{weekday}'
	),
	'byweekno' => array(
		'1' => ' 在第 %{weeks} 週',
		'else' => ' 在第 %{weeks} 週'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => '%{monthdays}',
	'nth_monthday' => array(
		'1' => '第 1 天',
		'else' => '第 %{n} 天'
	),
	'-nth_monthday' => array(
		'-1' => '最後一天',
		'else' => '倒數第 %{n} 天'
	),
	'byyearday' => array(
		'1' => '%{yeardays}',
		'else' => '%{yeardays}'
	),
	'nth_yearday' => array(
		'1' => '第 1 天',
		'else' => '第 %{n} 天'
	),
	'-nth_yearday' => array(
		'-1' => '最後一天',
		'else' => '倒數第 %{n} 天'
	),
	'byhour' => array(
		'1' => '%{hours}',
		'else' => '%{hours}'
	),
	'nth_hour' => '%{n}時',
	'byminute' => array(
		'1' => '%{minutes}',
		'else' => '%{minutes}'
	),
	'nth_minute' => '%{n}分',
	'bysecond' => array(
		'1' => '%{seconds}',
		'else' => '%{seconds}'
	),
	'nth_second' => '%{n}秒',
	'bysetpos' => '，但只套用到集合中的 %{setpos}',
	'nth_setpos' => array(
		'1' => '第 1 個',
		'else' => '第 %{n} 個'
	),
	'-nth_setpos' => array(
		'-1' => '最後 1 個',
		'else' => '倒數第 %{n} 個'
	)
);
