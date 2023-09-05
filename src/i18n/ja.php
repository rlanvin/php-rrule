<?php

/**
 * Translation file for Japanese language.
 *
 * Most strings can be an array, with a value as the key. The system will
 * pick the translation corresponding to the key. The key "else" will be picked
 * if no matching value is found. This is useful for plurals.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Taichi Kurihara <taichi.kurihara416@gmail.com>
 * @link https://github.com/Kuri-Tai/php-rrule
 */
return array(
    'yearly' => array(
        '1' => '毎年',
        'else' => '%{interval} 年ごと',
    ),
    'monthly' => array(
        '1' => '毎月',
        'else' => '%{interval} か月ごと',
    ),
    'weekly' => array(
        '1' => '毎週',
        '2' => '隔週',
        'else' => '%{interval} 週間ごと',
    ),
    'daily' => array(
        '1' => '毎日',
        '2' => '隔日',
        'else' => '%{interval} 日ごと',
    ),
    'hourly' => array(
        '1' => '毎時',
        'else' => '%{interval} 時間ごと',
    ),
    'minutely' => array(
        '1' => '毎分',
        'else' => '%{interval} 分ごと',
    ),
    'secondly' => array(
        '1' => '毎秒',
        'else' => '%{interval} 秒ごと',
    ),
    'dtstart' => ', %{date} から',
    'infinite' => ', 期日なし',
    'until' => ', %{date} まで',
    'count' => array(
        '1' => ', 1 回',
        'else' => ', %{count} 回',
    ),
    'and' => 'かつ ',
    'x_of_the_y' => array(
        'yearly' => 'その年の %{x}', // e.g. その年の 最初の 月曜日, もしくは その年の 最初の 日
        'monthly' => 'その月の %{x}',
    ),
    'bymonth' => ' %{months}',
    'months' => array(
        1 => '1月',
        2 => '2月',
        3 => '3月',
        4 => '4月',
        5 => '5月',
        6 => '6月',
        7 => '7月',
        8 => '8月',
        9 => '9月',
        10 => '10月',
        11 => '11月',
        12 => '12月',
    ),
    'byweekday' => ' %{weekdays}',
    'weekdays' => array(
        1 => '月曜日',
        2 => '火曜日',
        3 => '水曜日',
        4 => '木曜日',
        5 => '金曜日',
        6 => '土曜日',
        7 => '日曜日',
    ),
    'nth_weekday' => array(
        '1' => '最初の %{weekday}', // e.g. 最初の 月曜日
        'else' => '%{n}番目の %{weekday}',
    ),
    '-nth_weekday' => array(
        '-1' => '最後の %{weekday}', // e.g. 最後の 月曜日
        'else' => '最後から%{n}番目の %{weekday}',
    ),
    'byweekno' => array(
        '1' => ' 第%{weeks}週目',
        'else' => ' 第%{weeks}週目',
    ),
    'nth_weekno' => '%{n}',
    'bymonthday' => ' %{monthdays}',
    'nth_monthday' => array(
        '1' => '1番目の',
        'else' => '%{n}番目の',
    ),
    '-nth_monthday' => array(
        '-1' => '最後の',
        'else' => '最後から%{n}番目の',
    ),
    'byyearday' => array(
        '1' => ' %{yeardays}',
        'else' => ' %{yeardays}',
    ),
    'nth_yearday' => array(
        '1' => '1番目の',
        'else' => '%{n}番目の',
    ),
    '-nth_yearday' => array(
        '-1' => '最後の',
        'else' => '最後から%{n}番目の',
    ),
    'byhour' => array(
        '1' => ' %{hours}',
        'else' => ' %{hours}',
    ),
    'nth_hour' => '%{n}時',
    'byminute' => array(
        '1' => ' %{minutes}',
        'else' => ' %{minutes}',
    ),
    'nth_minute' => '%{n}分',
    'bysecond' => array(
        '1' => ' %{seconds}',
        'else' => ' %{seconds}',
    ),
    'nth_second' => '%{n}秒',
    'bysetpos' => ', ただし、そのうちの %{setpos} 該当するもののみ',
    'nth_setpos' => array(
        '1' => '最初の',
        'else' => '%{n}番目の',
    ),
    '-nth_setpos' => array(
        '-1' => '最後の',
        'else' => '最後から%{n}番目の',
    ),
);