<?php


/**
 * Translation file for Greek language.
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
    '1' => 'ετησίως',
    'else' => 'κάθε %{interval} χρόνια'
),
    'monthly' => array(
        '1' => 'μηνιαίως',
        'else' => 'κάθε %{interval} μήνες'
    ),
    'weekly' => array(
        '1' => 'εβδομαδιαίως',
        '2' => 'κάθε δεύτερη εβδομάδα',
        'else' => 'κάθε %{interval} εβδομάδες'
    ),
    'daily' => array(
        '1' => 'καθημερινώς',
        '2' => 'κάθε δεύτερη μέρα',
        'else' => 'κάθε %{interval} μέρες'
    ),
    'hourly' => array(
        '1' => 'ανά ώρα',
        'else' => 'κάθε %{interval} ώρες'
    ),
    'minutely' => array(
        '1' => 'ανά λεπτό',
        'else' => 'κάθε %{interval} λεπτά'
    ),
    'secondly' => array(
        '1' => 'ανά δευτερόλεπτο',
        'else' => 'κάθε %{interval} δευτερόλεπτα'
    ),
    'dtstart' => ', ξεκινώντας από %{date}',
    'timeofday' => ' στις %{date}',
    'startingtimeofday' => ' ξεκινώντας στις %{date}',
    'infinite' => ', για πάντα',
    'until' => ', μέχρι %{date}',
    'count' => array(
        '1' => ', μία φορά',
        'else' => ', %{count} φορές'
    ),
    'and' => 'και ',
    'x_of_the_y' => array(
        'yearly' => '%{x} του έτους',
        'monthly' => '%{x} του μήνα',
    ),
    'bymonth' => ' τον %{months}',
    'months' => array(
        1 => 'Ιανουάριος',
        2 => 'Φεβρουάριος',
        3 => 'Μάρτιος',
        4 => 'Απρίλιος',
        5 => 'Μάιος',
        6 => 'Ιούνιος',
        7 => 'Ιούλιος',
        8 => 'Αύγουστος',
        9 => 'Σεπτέμβριος',
        10 => 'Οκτώβριος',
        11 => 'Νοέμβριος',
        12 => 'Δεκέμβριος',
    ),
    'byweekday' => ' την %{weekdays}',
    'weekdays' => array(
        1 => 'Δευτέρα',
        2 => 'Τρίτη',
        3 => 'Τετάρτη',
        4 => 'Πέμπτη',
        5 => 'Παρασκευή',
        6 => 'Σάββατο',
        7 => 'Κυριακή',
    ),
    'nth_weekday' => array(
        '1' => 'την πρώτη %{weekday}',
        '2' => 'τη δεύτερη %{weekday}',
        '3' => 'την τρίτη %{weekday}',
        'else' => 'την %{n}η %{weekday}'
    ),
    '-nth_weekday' => array(
        '-1' => 'την τελευταία %{weekday}',
        '-2' => 'την προτελευταία %{weekday}',
        '-3' => 'την τρίτη από το τέλος %{weekday}',
        'else' => 'την %{n}η από το τέλος %{weekday}'
    ),
    'byweekno' => array(
        '1' => ' την εβδομάδα %{weeks}',
        'else' => ' τις εβδομάδες αριθμός %{weeks}'
    ),
    'nth_weekno' => '%{n}',
    'bymonthday' => ' την %{monthdays}',
    'nth_monthday' => array(
        '1' => 'την 1η',
        '2' => 'τη 2η',
        '3' => 'την 3η',
        '21' => 'την 21η',
        '22' => 'τη 22η',
        '23' => 'την 23η',
        '31' => 'την 31η',
        'else' => 'την %{n}η'
    ),
    '-nth_monthday' => array(
        '-1' => 'την τελευταία μέρα',
        '-2' => 'την προτελευταία μέρα',
        '-3' => 'την τρίτη από το τέλος μέρα',
        '-21' => 'την 21η από το τέλος μέρα',
        '-22' => 'την 22η από το τέλος μέρα',
        '-23' => 'την 23η από το τέλος μέρα',
        '-31' => 'την 31η από το τέλος μέρα',
        'else' => 'την %{n}η από το τέλος μέρα'
    ),
    'byyearday' => array(
        '1' => ' την %{yeardays}η μέρα',
        'else' => ' τις %{yeardays} μέρες'
    ),
    'nth_yearday' => array(
        '1' => 'την πρώτη',
        '2' => 'τη δεύτερη',
        '3' => 'την τρίτη',
        'else' => 'την %{n}η'
    ),
    '-nth_yearday' => array(
        '-1' => 'την τελευταία',
        '-2' => 'την προτελευταία',
        '-3' => 'την τρίτη από το τέλος',
        'else' => 'την %{n}η από το τέλος'
    ),
    'byhour' => array(
        '1' => ' την ώρα %{hours}',
        'else' => ' τις ώρες %{hours}'
    ),
    'nth_hour' => '%{n}η ώρα',
    'byminute' => array(
        '1' => ' στο λεπτό %{minutes}',
        'else' => ' στα λεπτά %{minutes}'
    ),
    'nth_minute' => '%{n}',
    'bysecond' => array(
        '1' => ' στο δευτερόλεπτο %{seconds}',
        'else' => ' στα δευτερόλεπτα %{seconds}'
    ),
    'nth_second' => '%{n}',
    'bysetpos' => ', αλλά μόνο %{setpos}η εμφάνιση αυτού του συνόλου',
    'nth_setpos' => array(
        '1' => 'την πρώτη',
        '2' => 'τη δεύτερη',
        '3' => 'την τρίτη',
        'else' => 'την %{n}η'
    ),
    '-nth_setpos' => array(
        '-1' => 'την τελευταία',
        '-2' => 'την προτελευταία',
        '-3' => 'την τρίτη από το τέλος',
        'else' => 'την %{n}η από το τέλος'
    )
);
