<?php

require __DIR__.'/vendor/autoload.php';

$rset = new RRule\RSet();

// $rset->addRdate();
$rset->addRRule(new RRule\RRule([
	'freq' => 'daily',
	'interval' => 3,
	'count' => 5
]));
$rset->addRRule(new RRule\RRule([
	'freq' => 'daily',
	'interval' => 2,
	'count' => 5
]));
$rset->addRRule(new RRule\RRule([
	'freq' => 'weekly',
	'count' => 5
]));

$rset->addDate(new DateTime('1983-12-20'));
$rset->addExdate(new DateTime());
$rset->addExrule(new RRule\RRule([
	'freq' => 'daily',
	'count' => 5
]));

// $rrule = new RRule\RRule([
// 	'freq' => 'daily',
// 	'interval' => 2,
// 	'count' => 5
// ]);
// foreach ( $rrule as $occurrence ) {
// 	var_dump($occurrence->format('Y-m-d H:i:s'));
// 	sleep(2);
// }
// $dt = new DateTime();
// var_dump($dt->format('Y-m-d H:i:s'));
// sleep(2);
// var_dump($dt->format('Y-m-d H:i:s'));
// die();
// $rule = new RRule\RRule([
// 	'freq' => 'weekly',
// 	'count' => 5
// ]);
// var_dump($rule->rfcString());
// var_dump($rule->humanReadable());
// var_dump($rule->getOccurrences());
// die();

// foreach ( $rset as $date ) {
// 	var_dump($date);
// 	fgets(STDIN);
// }

// foreach ( $rset as $date ) {
// 	echo $date->format('Y-m-d'),"\n";
// }

echo "Should be 1983-12-20 00:00:00.000000...\n";
var_dump($rset[0]);

echo "Should be 2016-03-27 23:11:00.000000...\n";
var_dump($rset[1]);

die();

// while ( ($date = $rset->iterate()) !== null ) {
foreach ( $rset as $date ) {
	echo "Calculated date\n";
	var_dump($date);
	fgets(STDIN);
}

echo "Done\n";

// // working example

// $dates1 = [new DateTime('2016-01-15'), new DateTime('2016-03-15')];
// $dates2 = [new DateTime('2016-01-10'), new DateTime('2016-02-10'), new DateTime('2016-05-10')];
// $dates3 = new RRule\RRule([
// 	'freq' => 'daily',
// 	'count' => 5
// ]);

// $iterator = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
// $iterator->attachIterator(new ArrayIterator($dates1));
// $iterator->attachIterator(new ArrayIterator($dates2));
// $iterator->attachIterator($dates3);

// $rlist = new SplMinHeap();
// $iterator->rewind();
// do {
// 	foreach ( $iterator->current() as $date ) {
// 		if ( $date !== null ) {
// 			$rlist->insert($date);
// 		}
// 	}
// 	$iterator->next();

// 	$date = $rlist->top();
// 	var_dump($date);
// 	$rlist->extract();

// } while ( ! $rlist->isEmpty() );