<?php
/**
 * Translation file for Dutch language.
 * Provided by Peter Melis <peter.melis@britelayer.com>
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
        '1' => 'jaarlijks',
        'else' => 'elke %{interval} jaar'
    ),
    'monthly' => array(
        '1' => 'maandelijks',
        'else' => 'elke %{interval} maanden'
    ),
    'weekly' => array(
        '1' => 'wekelijks',
        '2' => 'om de week',
        'else' => 'elke %{interval} weken'
    ),
    'daily' => array(
        '1' => 'dagelijks',
        '2' => 'om de dag',
        'else' => 'elke %{interval} dagen'
    ),
    'hourly' => array(
        '1' => 'elk uur',
        'else' => 'elke %{interval} uur'
    ),
    'minutely' => array(
        '1' => 'elke minuut',
        'else' => 'elke %{interval} minuten'
    ),
    'secondly' => array(
        '1' => 'elke seconde',
        'else' => 'elke %{interval} seconden'
    ),
    'dtstart' => ', wordt gestart vanaf %{date}',
    'infinite' => ', oneindig',
    'until' => ', tot en met %{date}',
    'count' => array(
        '1' => ', één keer',
        'else' => ', %{count} keren'
    ),
    'and' => 'en ',
    'x_of_the_y' => array(
        'yearly' => '%{x} van het jaar', // e.g. the first Monday of the year, or the first day of the year
        'monthly' => '%{x} van de maand',
    ),
    'bymonth' => ' in %{months}',
    'months' => array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maart',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Augustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'December',
    ),
    'byweekday' => ' op %{weekdays}',
    'weekdays' => array(
        1 => 'Maandag',
        2 => 'Dinsdag',
        3 => 'Woensdag',
        4 => 'Donderdag',
        5 => 'Vrijdag',
        6 => 'Zaterdag',
        7 => 'Zondag',
    ),
    'nth_weekday' => array(
        '1' => 'de eerste %{weekday}', // e.g. the first Monday
        '2' => 'de tweede %{weekday}',
        '3' => 'de derde %{weekday}',
        '8' => 'de achtste %{weekday}',
        'else' => 'de %{n}e %{weekday}'
    ),
    '-nth_weekday' => array(
        '-1' => 'de laatste %{weekday}', // e.g. the last Monday
        '-2' => 'de voorlaatste %{weekday}',
        '-3' => 'de twee-na-laatste %{weekday}',
        'else' => 'de %{n}e dag de laatste %{weekday}'
    ),
    'byweekno' => array(
        '1' => ' in week %{weeks}',
        'else' => ' in week nummer %{weeks}'
    ),
    'nth_weekno' => '%{n}',
    'bymonthday' => ' op %{monthdays}',
    'nth_monthday' => array(
        'else' => 'de %{n}e'
    ),
    '-nth_monthday' => array(
        '-1' => 'de laatste dag', // not so many options necessary for NL translation, but none removed
        '-2' => 'de voorlaatste dag',
        '-3' => 'de twee-na-laatste dag',
        'else' => 'de %{n}e tot de laatste dag'
    ),
    'byyearday' => array(
        '1' => ' op dag %{yeardays}',
        'else' => ' op de dagen %{yeardays}'
    ),
    'nth_yearday' => array(
        '1' => 'de eerste',
        '2' => 'de tweede',
        '3' => 'de derde',
        '8' => 'de achtste',
        'else' => 'de %{n}e'
    ),
    '-nth_yearday' => array(
        '-1' => 'de laatste',
        '-2' => 'de voorlaatste',
        '-3' => 'de twee-na-laatste',
        'else' => 'de %{n}e tot de laatste'
    ),
    'byhour' => array(
        '1' => ' op uur %{hours}',
        'else' => ' op uren %{hours}'
    ),
    'nth_hour' => '%{n}u',
    'byminute' => array(
        '1' => ' op minuut %{minutes}',
        'else' => ' op minuten %{minutes}'
    ),
    'nth_minute' => '%{n}',
    'bysecond' => array(
        '1' => ' op seconde %{seconds}',
        'else' => ' op seconden %{seconds}'
    ),
    'nth_second' => '%{n}',
    'bysetpos' => ', maar alleen %{setpos} match van deze set',
    'nth_setpos' => array(
        '1' => 'de eerste',
        '2' => 'de tweede',
        '3' => 'de derde',
        '8' => 'de achtste,',
		'else' => 'de %{n}e'
	),
	'-nth_setpos' => array(
        '-1' => 'de laatste',
        '-2' => 'de voorlaatste',
        '-3' => 'de twee-na-laatste',
        'else' => 'de %{n}e tot de laatste'
    )
);
