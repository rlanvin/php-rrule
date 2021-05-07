<?php declare(strict_types=1);

use RRule\RRule;

require(__DIR__ . '/../vendor/autoload.php');

$locale = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'en';


$rruleExamples = array(
	array(
		'FREQ' => 'YEARLY',
		'INTERVAL' => 1,
		'DTSTART' => '2015-06-01',
		'COUNT' => 6
	),
	array(
		'FREQ' => 'MONTHLY',
		'INTERVAL' => 3,
		'DTSTART' => date_create('1997-01-01')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 2,
		'BYDAY' => 'TU',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 2,
		'BYDAY' => 'TU',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 2,
		'BYDAY' => 'TU,FR',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 1,
		'BYDAY' => 'TU',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 1,
		'BYDAY' => 'TU',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'YEARLY',
		'COUNT' => 1,
		'BYDAY' => 'TU',
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'HOURLY',
		'COUNT' => 1,
		'BYHOUR' => 21,
		'BYMINUTE' => 10,
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'MINUTELY',
		'COUNT' => 1,
		'BYHOUR' => 21,
		'BYMINUTE' => 10,
		'DTSTART' => date_create('1997-09-02 09:00')
	),
	array(
		'FREQ' => 'MINUTELY',
		'BYHOUR' => 21,
		'BYMINUTE' => 10,
	),
	array(
		'FREQ' => 'SECONDLY',
		'BYHOUR' => 21,
		'BYMINUTE' => 10,
		'BYSECOND' => 10,
	),
);

foreach ($rruleExamples as $rruleExampleNr => $rruleDefinition) {
	$rrule = new RRule($rruleDefinition);
	echo '$rruleExamples #' . $rruleExampleNr . ': ' . $rrule->humanReadable(['locale' => $locale]) . "\n";


}


