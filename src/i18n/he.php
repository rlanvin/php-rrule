<?php

/**
 * Translation file for Hebrew language.
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @link https://github.com/rlanvin/php-rrule
 */
return array(
	'yearly' => array(
		'1' => 'כל שנה',
		'else' => 'כל  %{interval} שנים'
	),
	'monthly' => array(
		'1' => 'monthly',
		'else' => 'every %{interval} months'
	),
	'weekly' => array(
		'1' => 'כל שבוע',
		'2' => 'פעם בשבועיים',
		'else' => 'כל %{interval} שבועות'
	),
	'daily' => array(
		'1' => 'כל יום',
		'2' => 'פעם ביומיים',
		'else' => 'כל %{interval} ימים'
	),
	'hourly' => array(
		'1' => 'כל שעה',
		'else' => 'כל %{interval} שעות'
	),
	'minutely' => array(
		'1' => 'כל דקה',
		'else' => 'כל %{interval} דקות'
	),
	'secondly' => array(
		'1' => 'כל שניה',
		'else' => 'כל %{interval} שניות'
	),
	'dtstart' => ', החל מ%{date}',
	'infinite' => ', לעד',
	'until' => ', עד %{date}',
	'count' => array(
		'1' => ', פעם אחת',
		'else' => ', %{count} פעמים'
	),
	'and' => 'ו',
	'x_of_the_y' => array(
		'yearly' => '%{x} בשנה', // e.g. the first Monday of the year, or the first day of the year
		'monthly' => '%{x} בחודש',
	),
	'bymonth' => ' ב%{months}',
	'months' => array(
		1 => 'ינואר',
		2 => 'פברואר',
		3 => 'מארס',
		4 => 'אפריל',
		5 => 'מאי',
		6 => 'יוני',
		7 => 'יולי',
		8 => 'אוגוסט',
		9 => 'ספטמבר',
		10 => 'אוקטובר',
		11 => 'נובמבר',
		12 => 'דצמבר',
	),
	'byweekday' => ' ב%{weekdays}',
	'weekdays' => array(
		1 => "יום ב'",
		2 => "יום ג'",
		3 => "יום ד'",
		4 => "יום ה'",
		5 => "יום ו'",
		6 => "שבת",
		7 => "יום א'",
	),
	'shorten_weekdays_in_list' => true,
	'shorten_weekdays_days' => 'ימים ',
	'weekdays_shortened_for_list' => array(
		1 => "שני",
		2 => "שלישי",
		3 => "רביעי",
		4 => "חמישי",
		5 => "שישי",
		6 => "שבת",
		7 => "ראשון",
	),
	'nth_weekday' => array(
		'1' => '%{weekday} הראשון', // e.g. the first Monday
		'2' => '%{weekday} השני',
		'3' => '%{weekday} השלישי',
		'4' => '%{weekday} הרביעי',
		'5' => '%{weekday} החמישי',
		'6' => '%{weekday} השישי',
		'7' => '%{weekday} השביעי',
		'8' => '%{weekday} השמיני',
		'9' => '%{weekday} התשיעי',
		'10' => '%{weekday} העשירי',
		'else' => '%{weekday} ה-%{n}'
	),
	'-nth_weekday' => array(
		'-1' => '%{weekday} האחרון', // e.g. the last Monday
		'-2' => '%{weekday} לפני האחרון',
		'else' => '%{weekday} ה-%{n} לפני האחרון'
	),
	'byweekno' => array(
		'1' => ' בשבוע %{weeks}',
		'else' => ' בשבוע מספר %{weeks}'
	),
	'nth_weekno' => '%{n}',
	'bymonthday' => ' ב%{monthdays}',
	'nth_monthday' => array(
		'else' => 'ה-%{n}'
	),
	'-nth_monthday' => array(
		'-1' => 'היום האחרון',
		'-2' => 'היום הלפני-אחרון',
		'else' => 'היום ה-%{n} מהסוף'
	),
	'byyearday' => array(
		'1' => ' ביום%{yeardays}',
		'else' => ' בימים %{yeardays}'
	),
	'nth_yearday' => array(
		'1' => 'הראשון',
		'2' => 'השני',
		'3' => 'השלישי',
		'4' => 'הרביעי',
		'5' => 'החמישי',
		'6' => 'השישי',
		'7' => 'השביעי',
		'8' => 'השמיני',
		'9' => 'התשיעי',
		'10' => 'העשירי',
		'else' => 'ה-%{n}'
	),
	'-nth_yearday' => array(
		'-1' => 'האחרון',
		'-2' => 'לפני האחרון',
		'-3' => 'שניים לפני האחרון',
		'else' => 'ה %{n} מהסוף'
	),
	'byhour' => array(
		'1' => ' ב%{hours}',
		'else' => ' ב%{hours}'
	),
	'nth_hour' => '%{n}',
	'byminute' => array(
		'1' => ' בדקה %{minutes}',
		'else' => ' בדקות %{minutes}'
	),
	'nth_minute' => '%{n}',
	'bysecond' => array(
		'1' => ' בשניה %{seconds}',
		'else' => ' בשניות %{seconds}'
	),
	'nth_second' => '%{n}',
	'bysetpos' => ', אבל רק %{setpos} פעמים בסדרה זו',
	'nth_setpos' => array(
		'1' => 'הראשון',
		'2' => 'השני',
		'3' => 'השלישי',
		'4' => 'הרביעי',
		'5' => 'החמישי',
		'6' => 'השישי',
		'7' => 'השביעי',
		'8' => 'השמיני',
		'9' => 'התשיעי',
		'10' => 'העשירי',
		'else' => 'ה-%{n}'
	),
	'-nth_setpos' => array(
		'-1' => 'האחרון',
		'-2' => 'לפני האחרון',
		'-3' => 'שניים לפני האחרון',
		'else' => 'ה %{n} מהסוף'
	)
);
