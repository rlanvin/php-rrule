#!/usr/bin/env php
<?php declare(strict_types=1);

use RRule\RRule;

require(__DIR__ . '/../vendor/autoload.php');

$args = array_merge([
		'locale' => null,
		'rule' => null,
	],getopt('h',[
		'locale:',
		'rule:',
		'help'
	])
);

function display_usage() {
	echo <<<EOF
Usage:
	--locale <locale>	will output a set of example rules in one locale (default: en)
	--rule <rule>		will output a given rules in all available locales
EOF;
}

if (isset($args['h']) || isset($args['help'])) {
	display_usage();
	exit();
}

if (($args['locale'] && $args['rule']) || (!$args['locale'] && !$args['rule'])) {
	echo "Error: choose either --locale or --rule\n";
	display_usage();
	exit(1);
}

// display all the rules in one locale
if ($args['locale']) {
	$rules = array(
		array(
			'FREQ' => 'YEARLY',
			'INTERVAL' => 1,
			'DTSTART' => '2015-06-01',
			'COUNT' => 6
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
			'BYMONTH' => '1,5,8',
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'YEARLY',
			'BYYEARDAY' => [1,42,-1],
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'YEARLY',
			'BYWEEKNO' => '10,42',
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'MONTHLY',
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'MONTHLY',
			'INTERVAL' => 3,
			'BYMONTHDAY' => [5,10,-5,-1],
			'DTSTART' => date_create('1997-01-01'),
			'UNTIL' => date_create('1997-12-31')
		),
		array(
			'FREQ' => 'MONTHLY',
			'BYDAY' => '1MO,-1TU',
		),
		array(
			'FREQ' => 'WEEKLY',
			'COUNT' => 2,
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 2
		),
		array(
			'FREQ' => 'WEEKLY',
			'INTERVAL' => 3,
			'BYDAY' => 'MO,TU,FR',
			'BYSETPOS' => 1
		),
		array(
			'FREQ' => 'DAILY',
			'COUNT' => 2,
			'BYDAY' => 'TU,FR',
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'HOURLY',
			'COUNT' => 1,
			'BYHOUR' => [21,22],
			'BYMINUTE' => [10,20,30],
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'MINUTELY',
			'COUNT' => 1,
			'BYHOUR' => 21,
			'BYMINUTE' => 01,
			'DTSTART' => date_create('1997-09-02 09:00')
		),
		array(
			'FREQ' => 'MINUTELY',
			'BYHOUR' => 21,
			'BYMINUTE' => [0,10,20,30,40,50],
		),
		array(
			'FREQ' => 'SECONDLY',
			'BYHOUR' => 21,
			'BYMINUTE' => 10,
			'BYSECOND' => 10,
		),
	);

	foreach ($rules as $index => $definition) {
		$rrule = new RRule($definition);
		printf(
			"#%d\t%s\n",
			$index,
			$rrule->humanReadable(['locale' => $args['locale']])
		);
	}

	exit();
} 

// display all the locales for one rule
if ($args['rule']) {
	$rrule = new RRule($args['rule']);
	$locales = glob(__DIR__."/../src/i18n/*.php");
	
	foreach ($locales as $locale) {
		$locale = basename($locale,'.php');
		printf(
			"%s\t%s\n",
			$locale,
			$rrule->humanReadable(['locale' => $locale])
		);
	}
	exit();
}

// should never reach this
display_usage();
exit(1);