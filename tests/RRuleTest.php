<?php

namespace RRule\Tests;

use RRule\RRule;
use DateTime;
use DateTimeZone;
use ReflectionClass;
use stdClass;
use PHPUnit\Framework\TestCase;

class RRuleTest extends TestCase
{
	/**
	 * These rules are invalid according to the RFC
	 */
	public function invalidRules()
	{
		return array(
			array(array()),
			array(array('FOOBAR' => 'DAILY')),

			array(array('FREQ' => 'foobar')),
			'Invalid integer frequency' => [['FREQ' => 42]],
			array(array('FREQ' => 'DAILY', 'INTERVAL' => -1)),
			array(array('FREQ' => 'DAILY', 'INTERVAL' => 1.5)),
			array(array('FREQ' => 'DAILY', 'UNTIL' => 'foobar')),
			array(array('FREQ' => 'DAILY', 'COUNT' => -1)),
			array(array('FREQ' => 'DAILY', 'COUNT' => 1.5)),
			array(array('FREQ' => 'DAILY', 'UNTIL' => '2015-07-01', 'COUNT' => 1)),

			array(array('FREQ' => 'YEARLY', 'BYDAY' => '1MO,X')),
			// The BYDAY rule part MUST NOT be specified with a numeric value
			// when the FREQ rule part is not set to MONTHLY or YEARLY.
			array(array('FREQ' => 'DAILY', 'BYDAY' => array('1MO'))),
			array(array('FREQ' => 'DAILY', 'BYDAY' => array('1.5MO'))),
			array(array('FREQ' => 'WEEKLY', 'BYDAY' => array('1MO'))),
			// The BYDAY rule part MUST NOT be specified with a numeric value
			// with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.
			array(array('FREQ' => 'YEARLY', 'BYDAY' => array('1MO'), 'BYWEEKNO' => 20)),

			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => 0)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => 1.5)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => 32)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => -32)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => '1,A')),
			// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
			// part is set to WEEKLY.
			array(array('FREQ' => 'WEEKLY', 'BYMONTHDAY' => 1)),

			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => 0)),
			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => 1.5)),
			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => 367)),
			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => '1,A')),
			// The BYYEARDAY rule part MUST NOT be specified when the FREQ
			// rule part is set to DAILY, WEEKLY, or MONTHLY.
			array(array('FREQ' => 'DAILY', 'BYYEARDAY' => 1)),
			array(array('FREQ' => 'WEEKLY', 'BYYEARDAY' => 1)),
			array(array('FREQ' => 'MONTHLY', 'BYYEARDAY' => 1)),

			array(array('FREQ' => 'MONTHLY', 'BYMONTH' => 0)),
			array(array('FREQ' => 'MONTHLY', 'BYMONTH' => -1)),
			array(array('FREQ' => 'MONTHLY', 'BYMONTH' => 1.5)),
			array(array('FREQ' => 'MONTHLY', 'BYMONTH' => 13)),

			// BYSETPOS rule part MUST only be used in conjunction with another
			// BYxxx rule part.
			array(array('FREQ' => 'DAILY', 'BYSETPOS' => 1)),
			array(array('FREQ' => 'DAILY', 'BYDAY' => 'MO', 'BYSETPOS' => 1.5)),
			array(array('FREQ' => 'DAILY', 'BYDAY' => 'MO', 'BYSETPOS' => '1,A')),

			array(array('FREQ' => 'YEARLY', 'BYWEEKNO' => 0)),
			array(array('FREQ' => 'YEARLY', 'BYWEEKNO' => 1.5)),
			// The BYWEEKNO rule part MUST NOT be used when the FREQ rule part is set to anything other than YEARLY.
			'BYWEEKNO with FREQ not yearly' => [['FREQ' => 'DAILY', 'BYWEEKNO' => 1]],

			array(array('FREQ' => 'MONTHLY', 'BYHOUR' => -1)),
			array(array('FREQ' => 'MONTHLY', 'BYHOUR' => 1.5)),
			array(array('FREQ' => 'MONTHLY', 'BYHOUR' => 25)),

			array(array('FREQ' => 'MONTHLY', 'BYMINUTE' => -1)),
			array(array('FREQ' => 'MONTHLY', 'BYMINUTE' => 1.5)),
			array(array('FREQ' => 'MONTHLY', 'BYMINUTE' => 60)),

			array(array('FREQ' => 'MONTHLY', 'BYSECOND' => -1)),
			array(array('FREQ' => 'MONTHLY', 'BYSECOND' => 1.5)),
			array(array('FREQ' => 'MONTHLY', 'BYSECOND' => 61)),

			'Invalid WKST' => [['FREQ' => 'DAILY', 'WKST' => 'XX']],

			'Invalid DTSTART (invalid date)' => [['FREQ' => 'DAILY', 'DTSTART' => new stdClass()]]
		);
	}

	/**
	 * @dataProvider invalidRules
	 */
	public function testInvalidRules($rule)
	{
		$this->expectException(\InvalidArgumentException::class);
		new RRule($rule);
	}

	/**
	 * These rules are valid according to the RFC, just making sure that the lib doesn't reject them.
	 */
	public function validRules()
	{
		return array(
			// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule part is set to WEEKLY.
			array(array('FREQ' => 'WEEKLY', 'BYMONTHDAY' => array()))
		);
	}

	/**
	 * @dataProvider validRules
	 */
	public function testValidRules($rule)
	{
		$result = new RRule($rule);
		$this->assertInstanceOf('RRule\RRule', $result);
	}

	/**
	 * YEARLY rules, mostly taken from Python test suite.
	 */
	public function yearlyRules()
	{
		return array(
			array(array(),array(
				date_create('1997-09-02'),date_create('1998-09-02'), date_create('1999-09-02'))),
			array(array('INTERVAL' => 2), array(
				date_create('1997-09-02'),date_create('1999-09-02'),date_create('2001-09-02'))),
			array(array('DTSTART' => '2000-02-29'), array(
				date_create('2000-02-29'),date_create('2004-02-29'),date_create('2008-02-29'))),
			array(array('BYMONTH' => array(1,3)), array(
				date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02'))),
			array(array('BYMONTHDAY' => array(1,3)), array(
				date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03'))),
			array(array('BYMONTH' => array(1,3), 'BYMONTHDAY' => array(5,7)), array(
				date_create('1998-01-05'),date_create('1998-01-07'),date_create('1998-03-05'))),
			array(array('BYDAY' => array('TU','TH')), array(
				date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09'))),
			array(array('BYDAY' => array('SU')), array(
				date_create('1997-09-07'),date_create('1997-09-14'),date_create('1997-09-21'))),
			array(array('BYDAY' => array('1TU','-1TH')), array(
				date_create('1997-12-25'),date_create('1998-01-06'),date_create('1998-12-31'))),
			array(array('BYDAY' => array('3TU','-3TH')), array(
				date_create('1997-12-11'),date_create('1998-01-20'),date_create('1998-12-17'))),
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('TU','TH')), array(
				date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08'))),
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('1TU','-1TH')), array(
				date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03'))),
			// This is interesting because the TH(-3) ends up before the TU(3).
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('3TU','-3TH')), array(
				date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12'))),
			array(array('BYMONTHDAY' => array(1,3), 'BYDAY' => array('TU','TH')), array(
				date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03'))),
			array(array('BYMONTHDAY' => array(1,3), 'BYDAY' => array('TU','TH'), 'BYMONTH' => array(1,3)), array(
				date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01'))),
			'byyearday positive' => array(array('BYYEARDAY' => array(1,100,200,365), 'COUNT' => 4), array(
				date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19'))),
			'byyearday negative' => array(array('BYYEARDAY' => array(-365, -266, -166, -1), 'COUNT' => 4), array(
				date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19'))),
			'byyearday positive + bymonth' => array(array('BYYEARDAY' => array(1,100,200,365), 'BYMONTH' => array(4,7), 'COUNT' => 4), array(
				date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19'))),
			'byyearday negative + bymonth' => array(array('BYYEARDAY' => array(-365, -266, -166, -1), 'BYMONTH' => array(4,7), 'COUNT' => 4), array(
				date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19'))),
			'byyearday, 29 February' => [
				['BYYEARDAY' => '60'],
				[date_create('1998-03-01'), date_create('1999-03-01'), date_create('2000-02-29')]
			],
			'byyearday, 366th day' => [
				['BYYEARDAY' => '366'],
				[date_create('2000-12-31'), date_create('2004-12-31'), date_create('2008-12-31')]
			],
			'byyearday, -366th day' => [
				['BYYEARDAY' => '-366'],
				[date_create('2000-01-01'), date_create('2004-01-01'), date_create('2008-01-01')]
			],
			array(array('BYWEEKNO' => 20),array(
				date_create('1998-05-11'),date_create('1998-05-12'),date_create('1998-05-13'))),
			// That's a nice one. The first days of week number one may be in the last year.
			array(array('BYWEEKNO' => 1, 'BYDAY' => 'MO'), array(
				date_create('1997-12-29'), date_create('1999-01-04'), date_create('2000-01-03'))),
			// Another nice test. The last days of week number 52/53 may be in the next year.
			array(array('BYWEEKNO' => 52, 'BYDAY' => 'SU'), array(
				date_create('1997-12-28'), date_create('1998-12-27'), date_create('2000-01-02'))),
			array(array('BYWEEKNO' => -1, 'BYDAY' => 'SU'), array(
				date_create('1997-12-28'), date_create('1999-01-03'), date_create('2000-01-02'))),
			array(array('BYWEEKNO' => 53, 'BYDAY' => 'MO'), array(
				date_create('1998-12-28'), date_create('2004-12-27'), date_create('2009-12-28'))),

			// todo bysetpos

			array(array('BYHOUR' => '6,18'),array(
				date_create('1997-09-02 06:00:00'),
				date_create('1997-09-02 18:00:00'),
				date_create('1998-09-02 06:00:00'))),
			array(array('BYMINUTE'=> array(15, 30)), array(
				date_create('1997-09-02 00:15:00'),
				date_create('1997-09-02 00:30:00'),
				date_create('1998-09-02 00:15:00'))),
			array(array('BYSECOND' => array(10, 20)), array(
				date_create('1997-09-02 00:00:10'),
				date_create('1997-09-02 00:00:20'),
				date_create('1998-09-02 00:00:10'))),
			array(array('BYHOUR' => '6,18', 'BYMINUTE' => array(15, 30)),  array(
				date_create('1997-09-02 06:15:00'),
				date_create('1997-09-02 06:30:00'),
				date_create('1997-09-02 18:15:00'))),
			array(array('BYHOUR' => '6,18', 'BYSECOND' => array(10, 20)), array(
				date_create('1997-09-02 06:00:10'),
				date_create('1997-09-02 06:00:20'),
				date_create('1997-09-02 18:00:10'))),
			array(array('BYMINUTE' => array(15, 30), 'BYSECOND' => array(10, 20)), array(
				date_create('1997-09-02 00:15:10'),
				date_create('1997-09-02 00:15:20'),
				date_create('1997-09-02 00:30:10'))),
			array(array('BYHOUR'=>'6,18','BYMINUTE'=>array(15, 30),'BYSECOND'=>array(10, 20)),array(
				date_create('1997-09-02 06:15:10'),
				date_create('1997-09-02 06:15:20'),
				date_create('1997-09-02 06:30:10'))),
			array(array('BYMONTHDAY'=>15,'BYHOUR'=>'6,18','BYSETPOS'=>array(3, -3)),array(
				date_create('1997-11-15 18:00:00'),
				date_create('1998-02-15 06:00:00'),
				date_create('1998-11-15 18:00:00')))
		);
	}

	/**
	 * @dataProvider yearlyRules
	 */
	public function testYearly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').'in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').'in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * MONTHY rules, mostly taken from the Python test suite
	 */
	public function monthlyRules()
	{
		return array(
			array(array(),array(
				date_create('1997-09-02'),date_create('1997-10-02'),date_create('1997-11-02'))),
			array(array('INTERVAL'=>2),array(
				date_create('1997-09-02'),date_create('1997-11-02'),date_create('1998-01-02'))),
			'1.5 years' => array(array('INTERVAL'=>18),array(
				date_create('1997-09-02'),date_create('1999-03-02'),date_create('2000-09-02'))),
			'exactly 2 years in December' => [
				['INTERVAL'=> 24, 'DTSTART' => '1997-12-01'],
				[date_create('1997-12-01'),date_create('1999-12-01'),date_create('2001-12-01')]
			],
			array(array('BYMONTH' => '1,3'),array(
				date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02'))),
			array(array('BYMONTHDAY' => '1,3'),array(
				date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03'))),
			array(array('BYMONTHDAY' => array(5, 7), 'BYMONTH' => '1,3'), array(
				date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05'))),
			array(array('BYDAY' => array('TU', 'TH')), array(
				date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09'))),
			// Third Monday of the month
			array(array('BYDAY' => '3MO'),array(
				date_create('1997-09-15'),date_create('1997-10-20'),date_create('1997-11-17'))),
			array(array('BYDAY' => '1TU,-1TH'),array(
				date_create('1997-09-02'),date_create('1997-09-25'),date_create('1997-10-07'))),
			array(array('BYDAY' => '3TU,-3TH'),array(
				date_create('1997-09-11'),date_create('1997-09-16'),date_create('1997-10-16'))),
			array(array('BYDAY' => 'TU,TH', 'BYMONTH' => '1,3'),array(
				date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08'))),
			array(array('BYMONTH' => '1,3', 'BYDAY' => '1TU, -1TH'),array(
				date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03'))),
			array(array('BYMONTH' => '1,3', 'BYDAY' => '3TU, -3TH'),array(
				date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12'))),
			array(array('BYMONTHDAY' => '1,3', 'BYDAY' => array('TU', 'TH')), array(
				date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03'))),
			array(array('BYMONTH' => '1,3', 'BYMONTHDAY' => '1,3', 'BYDAY' => array('TU', 'TH')),array(
				date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01'))),

			// last workday of the month
			array(array('BYDAY'=>'MO,TU,WE,TH,FR','BYSETPOS'=>-1), array(
				date_create('1997-09-30'),
				date_create('1997-10-31'),
				date_create('1997-11-28'))),

			// first working day of the month, or previous Friday
			// see http://stackoverflow.com/questions/38170676/recurring-calendar-event-on-first-of-the-month/38314515
			array(array('BYDAY'=>'1MO,1TU,1WE,1TH,1FR,-1FR','BYMONTHDAY'=>'1,-1,-2'),
				array(date_create('1997-10-01'),date_create('1997-10-31'),date_create('1997-12-01'))),
			array(array('BYDAY'=>'1MO,1TU,1WE,1TH,FR','BYMONTHDAY'=>'1,-1,-2'),
				array(date_create('1997-10-01'),date_create('1997-10-31'),date_create('1997-12-01'))),

			array(array('BYHOUR'=> '6,18'),array(
				date_create('1997-09-02 06:00:00'),date_create('1997-09-02 18:00:00'),date_create('1997-10-02 06:00:00'))),
			array(array('BYMINUTE'=> '6,18'),array(
				date_create('1997-09-02 00:06:00'),date_create('1997-09-02 00:18:00'),date_create('1997-10-02 00:06:00'))),
			array(array('BYSECOND' => '6,18'),array(
				date_create('1997-09-02 00:00:06'),date_create('1997-09-02 00:00:18'),date_create('1997-10-02 00:00:06'))),
			array(array('BYHOUR'=>'6,18','BYMINUTE'=>'6,18'),array(
				date_create('1997-09-02 06:06:00'),date_create('1997-09-02 06:18:00'),date_create('1997-09-02 18:06:00'))),
			array(array('BYHOUR'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 06:00:06'),date_create('1997-09-02 06:00:18'),date_create('1997-09-02 18:00:06'))),
			array(array('BYMINUTE'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 00:06:06'),date_create('1997-09-02 00:06:18'),date_create('1997-09-02 00:18:06'))),
			array(array('BYHOUR'=>'6,18','BYMINUTE'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 06:06:06'),date_create('1997-09-02 06:06:18'),date_create('1997-09-02 06:18:06'))),
			array(array('BYMONTHDAY'=>array(13, 17),'BYHOUR'=>'6,18','BYSETPOS'=>array(3, -3)),array(
				date_create('1997-09-13 18:00'),date_create('1997-09-17 06:00'),date_create('1997-10-13 18:00'))),
			// avoid duplicates
			array(array('BYMONTHDAY'=>array(13, 17),'BYHOUR'=>'6,18','BYSETPOS'=>array(3, 3, -3)),array(
				date_create('1997-09-13 18:00'),date_create('1997-09-17 06:00'),date_create('1997-10-13 18:00'))),
			array(array('BYMONTHDAY'=>array(13, 17),'BYHOUR'=>'6,18','BYSETPOS'=>array(4, -1)),array(
				date_create('1997-09-17 18:00'),date_create('1997-10-17 18:00'),date_create('1997-11-17 18:00')))
		);
	}

	/**
	 * @dataProvider monthlyRules
	 */
	public function testMonthly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'MONTHLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * WEEKLY rules, mostly taken from the Python test suite
	 */
	public function weeklyRules()
	{
		return array(
			array(array(),array(
				date_create('1997-09-02'), date_create('1997-09-09'), date_create('1997-09-16'))),
			array(array('interval'=>2),array(
				date_create('1997-09-02'),date_create('1997-09-16'),date_create('1997-09-30'))),
			array(array('interval'=>20),array(
				date_create('1997-09-02'),date_create('1998-01-20'),date_create('1998-06-09'))),
			array(array('bymonth'=>'1,3'),array(
				date_create('1998-01-06'),date_create('1998-01-13'),date_create('1998-01-20'))),
			array(array('byday'=> array('TU', 'TH')),array(
				date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09'))),

			# This test is interesting, because it crosses the year
			# boundary in a weekly period to find day '1' as a
			# valid recurrence.
			array(array('bymonth'=>'1,3','byday'=>array('TU', 'TH')),array(
				date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08'))),

			array(array('byhour'=>'6,18'),array(
				date_create('1997-09-02 06:00:00'),date_create('1997-09-02 18:00:00'),date_create('1997-09-09 06:00:00'))),
			array(array('byminute'=>'6,18'),array(
				date_create('1997-09-02 00:06:00'),date_create('1997-09-02 00:18:00'),date_create('1997-09-09 00:06:00'))),
			array(array('bysecond'=> '6,18'),array(
				date_create('1997-09-02 00:00:06'),date_create('1997-09-02 00:00:18'),date_create('1997-09-09 00:00:06'))),
			array(array('byhour'=> '6,18','byminute'=>'6,18'),array(
				date_create('1997-09-02 06:06:00'),date_create('1997-09-02 06:18:00'),date_create('1997-09-02 18:06:00'))),
			array(array('byhour'=>'6,18','bysecond'=>'6,18', 'dtstart' => '1997-09-02 09:00:00'),array(
				date_create('1997-09-02 18:00:06'),
				date_create('1997-09-02 18:00:18'),
				date_create('1997-09-09 06:00:06'))),
			array(array('byminute'=>'6,18','bysecond'=>'6,18', 'dtstart' => '1997-09-02 09:00:00'),array(
				date_create('1997-09-02 09:06:06'),
				date_create('1997-09-02 09:06:18'),
				date_create('1997-09-02 09:18:06'))),
			array(array('byhour'=>'6,18','byminute'=>'6,18','bysecond'=>'6,18', 'dtstart' => '1997-09-02 09:00:00'),array(
				date_create('1997-09-02 18:06:06'),
				date_create('1997-09-02 18:06:18'),
				date_create('1997-09-02 18:18:06'))),
			array(array('byday'=>array('TU', 'TH'),'byhour'=>'6,18','bysetpos'=>array(3, -3), 'dtstart' => '1997-09-02 09:00:00'),array(
				date_create('1997-09-02 18:00:00'),
				date_create('1997-09-04 06:00:00'),
				date_create('1997-09-09 18:00:00')))
		);
	}

	/**
	 * @dataProvider weeklyRules
	 */
	public function testWeekly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'WEEKLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * DAILY rules, mostly taken from the Python test suite
	 */
	public function dailyRules()
	{
		return array(
			array(array(), array(
				date_create('1997-09-02'),date_create('1997-09-03'),date_create('1997-09-04'))),
			array(array('interval'=>2),array(
				date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-06'))),
			array(array('interval'=>92),array(
				date_create('1997-09-02'), date_create('1997-12-03'), date_create('1998-03-05'))),
			array(array('bymonth'=>'1,3'),array(
				date_create('1998-01-01'), date_create('1998-01-02'), date_create('1998-01-03'))),
			array(array('bymonthday'=>'1,3'),array(
				date_create('1997-09-03'), date_create('1997-10-01'), date_create('1997-10-03'))),
			array(array('bymonth'=>'1,3','bymonthday'=>array(5, 7)),array(
				date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05'))),
			array(array('byday'=>array('TU', 'TH')),array(
				date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09'))),
			array(array('bymonth'=> '1,3', 'byday'=> array('TU', 'TH')),array(
				date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08'))),
			array(array('bymonthday'=> '1,3', 'byday'=>array('TU', 'TH')),array(
				date_create('1998-01-01'), date_create('1998-02-03'), date_create('1998-03-03'))),
			array(array('bymonth'=>'1,3','bymonthday'=>'1,3','byday'=>array('TU', 'TH')),array(
				date_create('1998-01-01'), date_create('1998-03-03'), date_create('2001-03-01'))),

			// TODO BYSETPOS

			array(array('BYHOUR'=> '6,18'),array(
				date_create('1997-09-02 06:00:00'),date_create('1997-09-02 18:00:00'),date_create('1997-09-03 06:00:00'))),
			array(array('BYMINUTE'=> '6,18'),array(
				date_create('1997-09-02 00:06:00'),date_create('1997-09-02 00:18:00'),date_create('1997-09-03 00:06:00'))),
			array(array('BYSECOND' => '6,18'),array(
				date_create('1997-09-02 00:00:06'),date_create('1997-09-02 00:00:18'),date_create('1997-09-03 00:00:06'))),
			array(array('BYHOUR'=>'6,18','BYMINUTE'=>'6,18'),array(
				date_create('1997-09-02 06:06:00'),date_create('1997-09-02 06:18:00'),date_create('1997-09-02 18:06:00'))),
			array(array('BYHOUR'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 06:00:06'),date_create('1997-09-02 06:00:18'),date_create('1997-09-02 18:00:06'))),
			array(array('BYMINUTE'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 00:06:06'),date_create('1997-09-02 00:06:18'),date_create('1997-09-02 00:18:06'))),
			array(array('BYHOUR'=>'6,18','BYMINUTE'=>'6,18','BYSECOND'=>'6,18'),array(
				date_create('1997-09-02 06:06:06'),date_create('1997-09-02 06:06:18'),date_create('1997-09-02 06:18:06'))),
			array(array('BYHOUR'=>'6,18','byminute' => '15,45', 'BYSETPOS'=>array(3, -3), 'dtstart' => '1997-09-02, 09:00'),array(
				date_create('1997-09-02 18:15'),
				date_create('1997-09-03 06:45'),
				date_create('1997-09-03 18:15')))

		);
	}

	/**
	 * @dataProvider dailyRules
	 */
	public function testDaily($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'DAILY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * HOURLY rules, mostly taken from the Python test suite
	 */
	public function hourlyRules()
	{
		return array(
			array(array(), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 10:00:00'),
				date_create('1997-09-02 11:00:00'))),
			array(array('interval' => 2), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 11:00:00'),
				date_create('1997-09-02 13:00:00'))),
			array(array('interval' => 769), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-10-04 10:00:00'),
				date_create('1997-11-05 11:00:00'))),
			array(array('bymonth' => '1, 3'), array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 01:00:00'),
				date_create('1998-01-01 02:00:00'))),
			array(array('bymonthday'=>'1, 3'), array(
				date_create('1997-09-03 00:00:00'),
				date_create('1997-09-03 01:00:00'),
				date_create('1997-09-03 02:00:00'))),
			array(array('bymonth'=>'1, 3','bymonthday'=>'5, 7'),array(
				date_create('1998-01-05 00:00'),
				date_create('1998-01-05 01:00'),
				date_create('1998-01-05 02:00'))),
			array(array('byday'=>'TU, TH'), array(
				date_create('1997-09-02 09:00'),
				date_create('1997-09-02 10:00'),
				date_create('1997-09-02 11:00'))),
			array(array('bymonth'=> '1, 3', 'byday' => 'TU, TH'), array(
				date_create('1998-01-01 00:00'),
				date_create('1998-01-01 01:00'),
				date_create('1998-01-01 02:00'))),
			array(array('bymonthday'=>'1, 3','byday'=>'TU, TH'), array(
				date_create('1998-01-01 00:00'),
				date_create('1998-01-01 01:00'),
				date_create('1998-01-01 02:00'))),
			array(array('bymonth'=> '1, 3','bymonthday'=>'1, 3','byday'=>'TU, TH'), array(
				date_create('1998-01-01 00:00'),
				date_create('1998-01-01 01:00'),
				date_create('1998-01-01 02:00'))),
			array(array('count'=>4,'byyearday'=>'1, 100, 200, 365'), array(
				date_create('1997-12-31 00:00'),
				date_create('1997-12-31 01:00'),
				date_create('1997-12-31 02:00'),
				date_create('1997-12-31 03:00'))),
			array(array('count'=>4,'byyearday'=>'-365, -266, -166, -1'), array(
				date_create('1997-12-31 00:00'),
				date_create('1997-12-31 01:00'),
				date_create('1997-12-31 02:00'),
				date_create('1997-12-31 03:00'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'1, 100, 200, 365'), array(
				date_create('1998-04-10 00:00'),
				date_create('1998-04-10 01:00'),
				date_create('1998-04-10 02:00'),
				date_create('1998-04-10 03:00'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'-365, -266, -166, -1'), array(
				date_create('1998-04-10 00:00'),
				date_create('1998-04-10 01:00'),
				date_create('1998-04-10 02:00'),
				date_create('1998-04-10 03:00'))),
			'byyearday, 29 February' => [
				['BYYEARDAY' => '60'],
				[date_create('1998-03-01 00:00'), date_create('1998-03-01 01:00'), date_create('1998-03-01 02:00')]
			],
			'byyearday, 366th day' => [
				['BYYEARDAY' => '366'],
				[date_create('2000-12-31 00:00'), date_create('2000-12-31 01:00'), date_create('2000-12-31 02:00')]
			],
			'byyearday, -366th day' => [
				['BYYEARDAY' => '-366'],
				[date_create('2000-01-01 00:00'), date_create('2000-01-01 01:00'), date_create('2000-01-01 02:00')]
			],
			array(array('byhour'=>'6, 18'), array(
				date_create('1997-09-02 18:00'),
				date_create('1997-09-03 06:00'),
				date_create('1997-09-03 18:00'))),
			array(array('byminute'=>'6, 18'),array(
				date_create('1997-09-02 09:06'),
				date_create('1997-09-02 09:18'),
				date_create('1997-09-02 10:06'))),
			array(array('bysecond'=>'6, 18'),array(
				date_create('1997-09-02 09:00:06'),
				date_create('1997-09-02 09:00:18'),
				date_create('1997-09-02 10:00:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18'),array(
				date_create('1997-09-02 18:06'),
				date_create('1997-09-02 18:18'),
				date_create('1997-09-03 06:06'))),
			array(array('byhour'=>'6, 18','bysecond'=>'6, 18'),array(
				date_create('1997-09-02 18:00:06'),
				date_create('1997-09-02 18:00:18'),
				date_create('1997-09-03 06:00:06'))),
			array(array('byminute'=>'6, 18','bysecond'=>'6, 18'),array(
				date_create('1997-09-02 09:06:06'),
				date_create('1997-09-02 09:06:18'),
				date_create('1997-09-02 09:18:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18','bysecond'=>'6, 18'), array(
				date_create('1997-09-02 18:06:06'),
				date_create('1997-09-02 18:06:18'),
				date_create('1997-09-02 18:18:06'))),
			array(array('byminute'=>'15, 45','bysecond'=>'15, 45','bysetpos'=>'3, -3'), array(
				date_create('1997-09-02 09:15:45'),
				date_create('1997-09-02 09:45:15'),
				date_create('1997-09-02 10:15:45')))
		);
	}
	/**
	 * @dataProvider hourlyRules
	 */
	public function testHourly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'HOURLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02 09:00:00'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * MINUTELY rules, mostly taken from the Python test suite
	 */
	public function minutelyRules()
	{
		return array(
			array(array(), array(
				date_create('1997-09-02 09:00'),
				date_create('1997-09-02 09:01'),
				date_create('1997-09-02 09:02'))),
			array(array('interval'=>2), array(
				date_create('1997-09-02 09:00'),
				date_create('1997-09-02 09:02'),
				date_create('1997-09-02 09:04'))),
			array(array('interval'=>1501),array(
				date_create('1997-09-02 09:00'),
				date_create('1997-09-03 10:01'),
				date_create('1997-09-04 11:02'))),
			array(array('bymonth'=>'1, 3'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:01:00'),
				date_create('1998-01-01 00:02:00'))),
			array(array('bymonthday'=>'1, 3'), array(
				date_create('1997-09-03 00:00:00'),
				date_create('1997-09-03 00:01:00'),
				date_create('1997-09-03 00:02:00'))),
			array(array('bymonth'=>'1, 3','bymonthday'=>'5, 7'), array(
				date_create('1998-01-05 00:00:00'),
				date_create('1998-01-05 00:01:00'),
				date_create('1998-01-05 00:02:00'))),
			array(array('byday'=>'TU, TH'), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 09:01:00'),
				date_create('1997-09-02 09:02:00'))),
			array(array('bymonth'=>'1, 3','byday'=>'TU, TH'), array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:01:00'),
				date_create('1998-01-01 00:02:00'))),
			array(array('bymonthday'=>'1, 3','byday'=>'TU, TH'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:01:00'),
				date_create('1998-01-01 00:02:00'))),
			array(array('bymonth'=>'1, 3','bymonthday'=>'1, 3','byday'=> 'TU, TH'), array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:01:00'),
				date_create('1998-01-01 00:02:00'))),
			array(array('count'=>4, 'byyearday'=> '1, 100, 200, 365'), array(
				date_create('1997-12-31 00:00:00'),
				date_create('1997-12-31 00:01:00'),
				date_create('1997-12-31 00:02:00'),
				date_create('1997-12-31 00:03:00'))),
			array(array('count'=>4,'byyearday'=>'-365, -266, -166, -1'), array(
				date_create('1997-12-31 00:00:00'),
				date_create('1997-12-31 00:01:00'),
				date_create('1997-12-31 00:02:00'),
				date_create('1997-12-31 00:03:00'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'1, 100, 200, 365'),array(
				date_create('1998-04-10 00:00:00'),
				date_create('1998-04-10 00:01:00'),
				date_create('1998-04-10 00:02:00'),
				date_create('1998-04-10 00:03:00'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'-365, -266, -166, -1'),array(
				date_create('1998-04-10 00:00:00'),
				date_create('1998-04-10 00:01:00'),
				date_create('1998-04-10 00:02:00'),
				date_create('1998-04-10 00:03:00'))),
			array(array('byhour'=>'6, 18'),array(
				date_create('1997-09-02 18:00:00'),
				date_create('1997-09-02 18:01:00'),
				date_create('1997-09-02 18:02:00'))),
			array(array('byminute'=>'6, 18'),array(
				date_create('1997-09-02 09:06:00'),
				date_create('1997-09-02 09:18:00'),
				date_create('1997-09-02 10:06:00'))),
			array(array('bysecond'=> '6, 18'), array(
				date_create('1997-09-02 09:00:06'),
				date_create('1997-09-02 09:00:18'),
				date_create('1997-09-02 09:01:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18'), array(
				date_create('1997-09-02 18:06:00'),
				date_create('1997-09-02 18:18:00'),
				date_create('1997-09-03 06:06:00'))),
			array(array('byhour'=>'6, 18','bysecond'=>'6, 18'), array(
				date_create('1997-09-02 18:00:06'),
				date_create('1997-09-02 18:00:18'),
				date_create('1997-09-02 18:01:06'))),
			array(array('byminute'=>'6, 18','bysecond'=>'6, 18'),array(
				date_create('1997-09-02 09:06:06'),
				date_create('1997-09-02 09:06:18'),
				date_create('1997-09-02 09:18:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18','bysecond'=>'6, 18'),array(
				date_create('1997-09-02 18:06:06'),
				date_create('1997-09-02 18:06:18'),
				date_create('1997-09-02 18:18:06'))),
			array(array('bysecond'=>'15, 30, 45','bysetpos'=>'3, -3'),array(
				date_create('1997-09-02 09:00:15'),
				date_create('1997-09-02 09:00:45'),
				date_create('1997-09-02 09:01:15')))
		);
	}
	/**
	 * @dataProvider minutelyRules
	 */
	public function testMinutely($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'minutely',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02 09:00:00'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * SECONDLY rules, mostly taken from the Python test suite
	 */
	public function secondlyRules()
	{
		return array(
			array(array(), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 09:00:01'),
				date_create('1997-09-02 09:00:02'))),
			array(array('interval'=>2), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 09:00:02'),
				date_create('1997-09-02 09:00:04'))),
			array(array('interval'=>90061), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-03 10:01:01'),
				date_create('1997-09-04 11:02:02'))),
			array(array('bymonth'=>'1, 3'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:00:01'),
				date_create('1998-01-01 00:00:02'))),
			array(array('bymonthday'=>'1, 3'), array(
				date_create('1997-09-03 00:00:00'),
				date_create('1997-09-03 00:00:01'),
				date_create('1997-09-03 00:00:02'))),
			array(array('bymonth'=>'1, 3','bymonthday'=>'5, 7'),array(
				date_create('1998-01-05 00:00:00'),
				date_create('1998-01-05 00:00:01'),
				date_create('1998-01-05 00:00:02'))),
			array(array('byday'=>'TU, TH'), array(
				date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 09:00:01'),
				date_create('1997-09-02 09:00:02'))),
			array(array('bymonth'=>'1, 3','byday'=>'TU, TH'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:00:01'),
				date_create('1998-01-01 00:00:02'))),
			array(array('bymonthday'=>'1, 3','byday'=>'TU, TH'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:00:01'),
				date_create('1998-01-01 00:00:02'))),
			array(array('bymonth'=>'1, 3','bymonthday'=>'1, 3','byday'=>'TU, TH'),array(
				date_create('1998-01-01 00:00:00'),
				date_create('1998-01-01 00:00:01'),
				date_create('1998-01-01 00:00:02'))),
			array(array('count'=>4,'byyearday'=>'1, 100, 200, 365'),array(
				date_create('1997-12-31 00:00:00'),
				date_create('1997-12-31 00:00:01'),
				date_create('1997-12-31 00:00:02'),
				date_create('1997-12-31 00:00:03'))),
			array(array('count'=>4,'byyearday'=>'-365, -266, -166, -1'),array(
				date_create('1997-12-31 00:00:00'),
				date_create('1997-12-31 00:00:01'),
				date_create('1997-12-31 00:00:02'),
				date_create('1997-12-31 00:00:03'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'1, 100, 200, 365'),array(
				date_create('1998-04-10 00:00:00'),
				date_create('1998-04-10 00:00:01'),
				date_create('1998-04-10 00:00:02'),
				date_create('1998-04-10 00:00:03'))),
			array(array('count'=>4,'bymonth'=>'4, 7','byyearday'=>'-365, -266, -166, -1'),array(
				date_create('1998-04-10 00:00:00'),
				date_create('1998-04-10 00:00:01'),
				date_create('1998-04-10 00:00:02'),
				date_create('1998-04-10 00:00:03'))),
			array(array('byhour'=>'6, 18'),array(
				date_create('1997-09-02 18:00:00'),
				date_create('1997-09-02 18:00:01'),
				date_create('1997-09-02 18:00:02'))),
			array(array('byminute'=>'6, 18'), array(
				date_create('1997-09-02 09:06:00'),
				date_create('1997-09-02 09:06:01'),
				date_create('1997-09-02 09:06:02'))),
			array(array('bysecond'=>'6, 18'), array(
				date_create('1997-09-02 09:00:06'),
				date_create('1997-09-02 09:00:18'),
				date_create('1997-09-02 09:01:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18'), array(
				date_create('1997-09-02 18:06:00'),
				date_create('1997-09-02 18:06:01'),
				date_create('1997-09-02 18:06:02'))),
			array(array('byhour'=>'6, 18','bysecond'=>'6, 18'), array(
				date_create('1997-09-02 18:00:06'),
				date_create('1997-09-02 18:00:18'),
				date_create('1997-09-02 18:01:06'))),
			array(array('byminute'=>'6, 18','bysecond'=>'6, 18'), array(
				date_create('1997-09-02 09:06:06'),
				date_create('1997-09-02 09:06:18'),
				date_create('1997-09-02 09:18:06'))),
			array(array('byhour'=>'6, 18','byminute'=>'6, 18','bysecond'=>'6, 18'), array(
				date_create('1997-09-02 18:06:06'),
				date_create('1997-09-02 18:06:18'),
				date_create('1997-09-02 18:18:06'))),
			array(array('bysecond'=>'0','byminute'=>'1','dtstart'=>date_create('2010-03-22 12:01:00')), array(
				date_create('2010-03-22 12:01:00'),
				date_create('2010-03-22 13:01:00'),
				date_create('2010-03-22 14:01:00'))),
		);
	}
	/**
	 * @dataProvider secondlyRules
	 */
	public function testSecondly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'secondly',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02 09:00:00'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * Examples given in the RFC.
	 */
	public function rfcExamples()
	{
		return array(
			// Daily, for 10 occurrences.
			array(
				array('freq' => 'daily', 'count' => 10, 'dtstart' => date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney'))),
				array(date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-04 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-05 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-06 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-07 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-08 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-09 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-10 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-11 09:00:00',new DateTimeZone('Australia/Sydney')))
			),
			array(
				array('freq' => 'daily', 'count' => 10, 'dtstart' => date_create('2016-10-02 09:00:00',new DateTimeZone('Australia/Sydney'))),
				array(date_create('2016-10-02 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-03 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-04 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-05 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-06 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-07 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-08 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-09 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-10 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-10-11 09:00:00',new DateTimeZone('Australia/Sydney')))
			),
			array(
				array('freq' => 'daily', 'count' => 10, 'dtstart' => date_create('2016-04-02 09:00:00',new DateTimeZone('Australia/Sydney'))),
				array(date_create('2016-04-02 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-03 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-04 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-05 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-06 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-07 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-08 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-09 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-10 09:00:00',new DateTimeZone('Australia/Sydney')),
					date_create('2016-04-11 09:00:00',new DateTimeZone('Australia/Sydney')))
			),
			// Daily until December 24, 1997
			array(
				array('freq' => 'daily', 'dtstart' => '1997-09-02 09:00:00', 'until' => '1997-12-24 00:00:00'),
				array(date_create('1997-09-02 09:00:00'), date_create('1997-09-03 09:00:00'),
				date_create('1997-09-04 09:00:00'), date_create('1997-09-05 09:00:00'),
				date_create('1997-09-06 09:00:00'), date_create('1997-09-07 09:00:00'),
				date_create('1997-09-08 09:00:00'), date_create('1997-09-09 09:00:00'),
				date_create('1997-09-10 09:00:00'), date_create('1997-09-11 09:00:00'),
				date_create('1997-09-12 09:00:00'), date_create('1997-09-13 09:00:00'),
				date_create('1997-09-14 09:00:00'), date_create('1997-09-15 09:00:00'),
				date_create('1997-09-16 09:00:00'), date_create('1997-09-17 09:00:00'),
				date_create('1997-09-18 09:00:00'), date_create('1997-09-19 09:00:00'),
				date_create('1997-09-20 09:00:00'), date_create('1997-09-21 09:00:00'),
				date_create('1997-09-22 09:00:00'), date_create('1997-09-23 09:00:00'),
				date_create('1997-09-24 09:00:00'), date_create('1997-09-25 09:00:00'),
				date_create('1997-09-26 09:00:00'), date_create('1997-09-27 09:00:00'),
				date_create('1997-09-28 09:00:00'), date_create('1997-09-29 09:00:00'),
				date_create('1997-09-30 09:00:00'), date_create('1997-10-01 09:00:00'),
				date_create('1997-10-02 09:00:00'), date_create('1997-10-03 09:00:00'),
				date_create('1997-10-04 09:00:00'), date_create('1997-10-05 09:00:00'),
				date_create('1997-10-06 09:00:00'), date_create('1997-10-07 09:00:00'),
				date_create('1997-10-08 09:00:00'), date_create('1997-10-09 09:00:00'),
				date_create('1997-10-10 09:00:00'), date_create('1997-10-11 09:00:00'),
				date_create('1997-10-12 09:00:00'), date_create('1997-10-13 09:00:00'),
				date_create('1997-10-14 09:00:00'), date_create('1997-10-15 09:00:00'),
				date_create('1997-10-16 09:00:00'), date_create('1997-10-17 09:00:00'),
				date_create('1997-10-18 09:00:00'), date_create('1997-10-19 09:00:00'),
				date_create('1997-10-20 09:00:00'), date_create('1997-10-21 09:00:00'),
				date_create('1997-10-22 09:00:00'), date_create('1997-10-23 09:00:00'),
				date_create('1997-10-24 09:00:00'), date_create('1997-10-25 09:00:00'),
				date_create('1997-10-26 09:00:00'), date_create('1997-10-27 09:00:00'),
				date_create('1997-10-28 09:00:00'), date_create('1997-10-29 09:00:00'),
				date_create('1997-10-30 09:00:00'), date_create('1997-10-31 09:00:00'),
				date_create('1997-11-01 09:00:00'),
				date_create('1997-11-02 09:00:00'), date_create('1997-11-03 09:00:00'),
				date_create('1997-11-04 09:00:00'), date_create('1997-11-05 09:00:00'),
				date_create('1997-11-06 09:00:00'), date_create('1997-11-07 09:00:00'),
				date_create('1997-11-08 09:00:00'), date_create('1997-11-09 09:00:00'),
				date_create('1997-11-10 09:00:00'), date_create('1997-11-11 09:00:00'),
				date_create('1997-11-12 09:00:00'), date_create('1997-11-13 09:00:00'),
				date_create('1997-11-14 09:00:00'), date_create('1997-11-15 09:00:00'),
				date_create('1997-11-16 09:00:00'), date_create('1997-11-17 09:00:00'),
				date_create('1997-11-18 09:00:00'), date_create('1997-11-19 09:00:00'),
				date_create('1997-11-20 09:00:00'), date_create('1997-11-21 09:00:00'),
				date_create('1997-11-22 09:00:00'), date_create('1997-11-23 09:00:00'),
				date_create('1997-11-24 09:00:00'), date_create('1997-11-25 09:00:00'),
				date_create('1997-11-26 09:00:00'), date_create('1997-11-27 09:00:00'),
				date_create('1997-11-28 09:00:00'), date_create('1997-11-29 09:00:00'),
				date_create('1997-11-30 09:00:00'), date_create('1997-12-01 09:00:00'),
				date_create('1997-12-02 09:00:00'), date_create('1997-12-03 09:00:00'),
				date_create('1997-12-04 09:00:00'), date_create('1997-12-05 09:00:00'),
				date_create('1997-12-06 09:00:00'), date_create('1997-12-07 09:00:00'),
				date_create('1997-12-08 09:00:00'), date_create('1997-12-09 09:00:00'),
				date_create('1997-12-10 09:00:00'), date_create('1997-12-11 09:00:00'),
				date_create('1997-12-12 09:00:00'), date_create('1997-12-13 09:00:00'),
				date_create('1997-12-14 09:00:00'), date_create('1997-12-15 09:00:00'),
				date_create('1997-12-16 09:00:00'), date_create('1997-12-17 09:00:00'),
				date_create('1997-12-18 09:00:00'), date_create('1997-12-19 09:00:00'),
				date_create('1997-12-20 09:00:00'), date_create('1997-12-21 09:00:00'),
				date_create('1997-12-22 09:00:00'), date_create('1997-12-23 09:00:00'))
			),
			// Every other day, 5 occurrences.
			array(
				array('freq' => 'daily', 'interval' => 2, 'count' => 5, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				 date_create('1997-09-04 09:00:00'),
				 date_create('1997-09-06 09:00:00'),
				 date_create('1997-09-08 09:00:00'),
				 date_create('1997-09-10 09:00:00'))
			),
			// Every 10 days, 5 occurrences.
			array(
				array('freq' => 'daily', 'interval' => 10, 'count' => 5, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				 date_create('1997-09-12 09:00:00'),
				 date_create('1997-09-22 09:00:00'),
				 date_create('1997-10-02 09:00:00'),
				 date_create('1997-10-12 09:00:00'))
			),
			// Everyday in January, for 3 years.
			array(
				array('freq' => 'yearly', 'bymonth' => 1, 'byday' => 'MO,TU,WE,TH,FR,SA,SU', 'dtstart' => '1997-09-02 09:00:00', 'until' => '2000-01-31 09:00:00'),
				array(date_create('1998-01-01 09:00:00'),
				date_create('1998-01-02 09:00:00'), date_create('1998-01-03 09:00:00'),
				date_create('1998-01-04 09:00:00'), date_create('1998-01-05 09:00:00'),
				date_create('1998-01-06 09:00:00'), date_create('1998-01-07 09:00:00'),
				date_create('1998-01-08 09:00:00'), date_create('1998-01-09 09:00:00'),
				date_create('1998-01-10 09:00:00'), date_create('1998-01-11 09:00:00'),
				date_create('1998-01-12 09:00:00'), date_create('1998-01-13 09:00:00'),
				date_create('1998-01-14 09:00:00'), date_create('1998-01-15 09:00:00'),
				date_create('1998-01-16 09:00:00'), date_create('1998-01-17 09:00:00'),
				date_create('1998-01-18 09:00:00'), date_create('1998-01-19 09:00:00'),
				date_create('1998-01-20 09:00:00'), date_create('1998-01-21 09:00:00'),
				date_create('1998-01-22 09:00:00'), date_create('1998-01-23 09:00:00'),
				date_create('1998-01-24 09:00:00'), date_create('1998-01-25 09:00:00'),
				date_create('1998-01-26 09:00:00'), date_create('1998-01-27 09:00:00'),
				date_create('1998-01-28 09:00:00'), date_create('1998-01-29 09:00:00'),
				date_create('1998-01-30 09:00:00'), date_create('1998-01-31 09:00:00'),
				date_create('1999-01-01 09:00:00'),
				date_create('1999-01-02 09:00:00'), date_create('1999-01-03 09:00:00'),
				date_create('1999-01-04 09:00:00'), date_create('1999-01-05 09:00:00'),
				date_create('1999-01-06 09:00:00'), date_create('1999-01-07 09:00:00'),
				date_create('1999-01-08 09:00:00'), date_create('1999-01-09 09:00:00'),
				date_create('1999-01-10 09:00:00'), date_create('1999-01-11 09:00:00'),
				date_create('1999-01-12 09:00:00'), date_create('1999-01-13 09:00:00'),
				date_create('1999-01-14 09:00:00'), date_create('1999-01-15 09:00:00'),
				date_create('1999-01-16 09:00:00'), date_create('1999-01-17 09:00:00'),
				date_create('1999-01-18 09:00:00'), date_create('1999-01-19 09:00:00'),
				date_create('1999-01-20 09:00:00'), date_create('1999-01-21 09:00:00'),
				date_create('1999-01-22 09:00:00'), date_create('1999-01-23 09:00:00'),
				date_create('1999-01-24 09:00:00'), date_create('1999-01-25 09:00:00'),
				date_create('1999-01-26 09:00:00'), date_create('1999-01-27 09:00:00'),
				date_create('1999-01-28 09:00:00'), date_create('1999-01-29 09:00:00'),
				date_create('1999-01-30 09:00:00'), date_create('1999-01-31 09:00:00'),
				date_create('2000-01-01 09:00:00'),
				date_create('2000-01-02 09:00:00'), date_create('2000-01-03 09:00:00'),
				date_create('2000-01-04 09:00:00'), date_create('2000-01-05 09:00:00'),
				date_create('2000-01-06 09:00:00'), date_create('2000-01-07 09:00:00'),
				date_create('2000-01-08 09:00:00'), date_create('2000-01-09 09:00:00'),
				date_create('2000-01-10 09:00:00'), date_create('2000-01-11 09:00:00'),
				date_create('2000-01-12 09:00:00'), date_create('2000-01-13 09:00:00'),
				date_create('2000-01-14 09:00:00'), date_create('2000-01-15 09:00:00'),
				date_create('2000-01-16 09:00:00'), date_create('2000-01-17 09:00:00'),
				date_create('2000-01-18 09:00:00'), date_create('2000-01-19 09:00:00'),
				date_create('2000-01-20 09:00:00'), date_create('2000-01-21 09:00:00'),
				date_create('2000-01-22 09:00:00'), date_create('2000-01-23 09:00:00'),
				date_create('2000-01-24 09:00:00'), date_create('2000-01-25 09:00:00'),
				date_create('2000-01-26 09:00:00'), date_create('2000-01-27 09:00:00'),
				date_create('2000-01-28 09:00:00'), date_create('2000-01-29 09:00:00'),
				date_create('2000-01-30 09:00:00'), date_create('2000-01-31 09:00:00'))
			),
			// Same thing, in another way
			array(
				array('freq' => 'daily', 'bymonth' => 1, 'dtstart' => '1997-09-02 09:00:00', 'until' => '2000-01-31 09:00:00'),
				array(date_create('1998-01-01 09:00:00'),
				date_create('1998-01-02 09:00:00'), date_create('1998-01-03 09:00:00'),
				date_create('1998-01-04 09:00:00'), date_create('1998-01-05 09:00:00'),
				date_create('1998-01-06 09:00:00'), date_create('1998-01-07 09:00:00'),
				date_create('1998-01-08 09:00:00'), date_create('1998-01-09 09:00:00'),
				date_create('1998-01-10 09:00:00'), date_create('1998-01-11 09:00:00'),
				date_create('1998-01-12 09:00:00'), date_create('1998-01-13 09:00:00'),
				date_create('1998-01-14 09:00:00'), date_create('1998-01-15 09:00:00'),
				date_create('1998-01-16 09:00:00'), date_create('1998-01-17 09:00:00'),
				date_create('1998-01-18 09:00:00'), date_create('1998-01-19 09:00:00'),
				date_create('1998-01-20 09:00:00'), date_create('1998-01-21 09:00:00'),
				date_create('1998-01-22 09:00:00'), date_create('1998-01-23 09:00:00'),
				date_create('1998-01-24 09:00:00'), date_create('1998-01-25 09:00:00'),
				date_create('1998-01-26 09:00:00'), date_create('1998-01-27 09:00:00'),
				date_create('1998-01-28 09:00:00'), date_create('1998-01-29 09:00:00'),
				date_create('1998-01-30 09:00:00'), date_create('1998-01-31 09:00:00'),
				date_create('1999-01-01 09:00:00'),
				date_create('1999-01-02 09:00:00'), date_create('1999-01-03 09:00:00'),
				date_create('1999-01-04 09:00:00'), date_create('1999-01-05 09:00:00'),
				date_create('1999-01-06 09:00:00'), date_create('1999-01-07 09:00:00'),
				date_create('1999-01-08 09:00:00'), date_create('1999-01-09 09:00:00'),
				date_create('1999-01-10 09:00:00'), date_create('1999-01-11 09:00:00'),
				date_create('1999-01-12 09:00:00'), date_create('1999-01-13 09:00:00'),
				date_create('1999-01-14 09:00:00'), date_create('1999-01-15 09:00:00'),
				date_create('1999-01-16 09:00:00'), date_create('1999-01-17 09:00:00'),
				date_create('1999-01-18 09:00:00'), date_create('1999-01-19 09:00:00'),
				date_create('1999-01-20 09:00:00'), date_create('1999-01-21 09:00:00'),
				date_create('1999-01-22 09:00:00'), date_create('1999-01-23 09:00:00'),
				date_create('1999-01-24 09:00:00'), date_create('1999-01-25 09:00:00'),
				date_create('1999-01-26 09:00:00'), date_create('1999-01-27 09:00:00'),
				date_create('1999-01-28 09:00:00'), date_create('1999-01-29 09:00:00'),
				date_create('1999-01-30 09:00:00'), date_create('1999-01-31 09:00:00'),
				date_create('2000-01-01 09:00:00'),
				date_create('2000-01-02 09:00:00'), date_create('2000-01-03 09:00:00'),
				date_create('2000-01-04 09:00:00'), date_create('2000-01-05 09:00:00'),
				date_create('2000-01-06 09:00:00'), date_create('2000-01-07 09:00:00'),
				date_create('2000-01-08 09:00:00'), date_create('2000-01-09 09:00:00'),
				date_create('2000-01-10 09:00:00'), date_create('2000-01-11 09:00:00'),
				date_create('2000-01-12 09:00:00'), date_create('2000-01-13 09:00:00'),
				date_create('2000-01-14 09:00:00'), date_create('2000-01-15 09:00:00'),
				date_create('2000-01-16 09:00:00'), date_create('2000-01-17 09:00:00'),
				date_create('2000-01-18 09:00:00'), date_create('2000-01-19 09:00:00'),
				date_create('2000-01-20 09:00:00'), date_create('2000-01-21 09:00:00'),
				date_create('2000-01-22 09:00:00'), date_create('2000-01-23 09:00:00'),
				date_create('2000-01-24 09:00:00'), date_create('2000-01-25 09:00:00'),
				date_create('2000-01-26 09:00:00'), date_create('2000-01-27 09:00:00'),
				date_create('2000-01-28 09:00:00'), date_create('2000-01-29 09:00:00'),
				date_create('2000-01-30 09:00:00'), date_create('2000-01-31 09:00:00'))
			),
			// Weekly for 10 occurrences:
			array(
				array('freq' => 'weekly', 'count' => 10, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-07 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-21 09:00:00'),
				date_create('1997-10-28 09:00:00'),
				date_create('1997-11-04 09:00:00'))
			),
			// Every other week, 6 occurrences.
			array(
				array('freq' => 'weekly', 'interval' => 2, 'count' => 6, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-28 09:00:00'),
				date_create('1997-11-11 09:00:00'))
			),
			// Weekly on Tuesday and Thursday for 5 weeks, week starting on Sunday.
			array(
				array('freq' => 'weekly', 'count' => 10, 'wkst' => 'SU', 'byday' => 'TU,TH', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-04 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-11 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-18 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-25 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-02 09:00:00'))
			),
			// Every other week on Tuesday and Thursday, for 8 occurrences, week starting on Sunday
			array(
				array('freq' => 'weekly', 'interval' => 2, 'count' => 8, 'wkst' => 'SU', 'byday' => 'TU,TH', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-04 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-18 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-02 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-16 09:00:00'))
			),
			// Monthly on the 1st Friday for ten occurrences.
			array(
				array('freq' => 'monthly', 'count' => 10, 'byday' => '1FR', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-05 09:00:00'),
				date_create('1997-10-03 09:00:00'),
				date_create('1997-11-07 09:00:00'),
				date_create('1997-12-05 09:00:00'),
				date_create('1998-01-02 09:00:00'),
				date_create('1998-02-06 09:00:00'),
				date_create('1998-03-06 09:00:00'),
				date_create('1998-04-03 09:00:00'),
				date_create('1998-05-01 09:00:00'),
				date_create('1998-06-05 09:00:00'))
			),
			// Every other month on the 1st and last Sunday of the month for 10 occurrences.
			array(
				array('freq' => 'monthly', 'interval' => 2, 'count' => 10, 'byday' => '1SU,-1SU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-07 09:00:00'),
				date_create('1997-09-28 09:00:00'),
				date_create('1997-11-02 09:00:00'),
				date_create('1997-11-30 09:00:00'),
				date_create('1998-01-04 09:00:00'),
				date_create('1998-01-25 09:00:00'),
				date_create('1998-03-01 09:00:00'),
				date_create('1998-03-29 09:00:00'),
				date_create('1998-05-03 09:00:00'),
				date_create('1998-05-31 09:00:00'))
			),
			// Monthly on the second to last Monday of the month for 6 months.
			array(
				array('freq' => 'monthly', 'count' => 6, 'byday' => '-2MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-22 09:00:00'),
				date_create('1997-10-20 09:00:00'),
				date_create('1997-11-17 09:00:00'),
				date_create('1997-12-22 09:00:00'),
				date_create('1998-01-19 09:00:00'),
				date_create('1998-02-16 09:00:00'))
			),
			// Monthly on the third to the last day of the month, for 6 months.
			array(
				array('freq' => 'monthly', 'count' => 6, 'bymonthday' => '-3', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-28 09:00:00'),
				date_create('1997-10-29 09:00:00'),
				date_create('1997-11-28 09:00:00'),
				date_create('1997-12-29 09:00:00'),
				date_create('1998-01-29 09:00:00'),
				date_create('1998-02-26 09:00:00'))
			),
			// Monthly on the 2nd and 15th of the month for 5 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 5, 'bymonthday' => '2,15', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-15 09:00:00'),
				date_create('1997-10-02 09:00:00'),
				date_create('1997-10-15 09:00:00'),
				date_create('1997-11-02 09:00:00'))
			),
			// Monthly on the first and last day of the month for 3 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 5, 'bymonthday' => '-1,1', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-30 09:00:00'),
				date_create('1997-10-01 09:00:00'),
				date_create('1997-10-31 09:00:00'),
				date_create('1997-11-01 09:00:00'),
				date_create('1997-11-30 09:00:00'))
			),
			// Every 18 months on the 10th thru 15th of the month for 10 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 10, 'interval' => 18, 'bymonthday' => range(10,15), 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-10 09:00:00'),
				date_create('1997-09-11 09:00:00'),
				date_create('1997-09-12 09:00:00'),
				date_create('1997-09-13 09:00:00'),
				date_create('1997-09-14 09:00:00'),
				date_create('1997-09-15 09:00:00'),
				date_create('1999-03-10 09:00:00'),
				date_create('1999-03-11 09:00:00'),
				date_create('1999-03-12 09:00:00'),
				date_create('1999-03-13 09:00:00'))),
			// Every Tuesday, every other month, 6 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 6, 'interval' => 2, 'byday' => 'TU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-11-04 09:00:00'))),
			// Yearly in June and July for 10 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 10, 'bymonth' => '6,7', 'dtstart' => '1997-06-10 09:00:00'),
				array(date_create('1997-06-10 09:00:00'),date_create('1997-07-10 09:00:00'),
				date_create('1998-06-10 09:00:00'),date_create('1998-07-10 09:00:00'),
				date_create('1999-06-10 09:00:00'),date_create('1999-07-10 09:00:00'),
				date_create('2000-06-10 09:00:00'),date_create('2000-07-10 09:00:00'),
				date_create('2001-06-10 09:00:00'),date_create('2001-07-10 09:00:00'))
			),
			// Every 3rd year on the 1st, 100th and 200th day for 4 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 4, 'interval' => '3', 'byyearday' => '1,100,200', 'dtstart' => '1997-01-01 09:00:00'),
				array(date_create('1997-01-01 09:00:00'),
				date_create('1997-04-10 09:00:00'),
				date_create('1997-07-19 09:00:00'),
				date_create('2000-01-01 09:00:00'))
			),
			// Every 20th Monday of the year, 3 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byday' => '20MO', 'dtstart' => '1997-05-19 09:00:00'),
				array(date_create('1997-05-19 09:00:00'),
				date_create('1998-05-18 09:00:00'),
				date_create('1999-05-17 09:00:00'))
			),
			// Monday of week number 20 (where the default start of the week is Monday), 3 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 20, 'byday' => 'MO', 'dtstart' => '1997-05-12 09:00:00'),
				array(date_create('1997-05-12 09:00:00'),
				date_create('1998-05-11 09:00:00'),
				date_create('1999-05-17 09:00:00'))
			),
			// Every Thursday in March
			array(
				array('freq' => 'yearly', 'byday' => 'TH', 'bymonth' => 3, 'dtstart' => '1997-03-13 09:00:00', 'until' => '2000-01-01'),
				array(date_create('1997-03-13 09:00:00'),
				date_create('1997-03-20 09:00:00'),
				date_create('1997-03-27 09:00:00'),
				date_create('1998-03-05 09:00:00'),
				date_create('1998-03-12 09:00:00'),
				date_create('1998-03-19 09:00:00'),
				date_create('1998-03-26 09:00:00'),
				date_create('1999-03-04 09:00:00'),
				date_create('1999-03-11 09:00:00'),
				date_create('1999-03-18 09:00:00'),
				date_create('1999-03-25 09:00:00'))
			),
			// Every Thursday, but only during June, July, and August
			array(
				array('freq' => 'yearly', 'byday' => 'TH', 'bymonth' => array(6,7,8), 'dtstart' => '1997-01-01 09:00:00', 'until' => '1999-01-01'),
				array(date_create('1997-06-05 09:00:00'),
				date_create('1997-06-12 09:00:00'),
				date_create('1997-06-19 09:00:00'),
				date_create('1997-06-26 09:00:00'),
				date_create('1997-07-03 09:00:00'),
				date_create('1997-07-10 09:00:00'),
				date_create('1997-07-17 09:00:00'),
				date_create('1997-07-24 09:00:00'),
				date_create('1997-07-31 09:00:00'),
				date_create('1997-08-07 09:00:00'),
				date_create('1997-08-14 09:00:00'),
				date_create('1997-08-21 09:00:00'),
				date_create('1997-08-28 09:00:00'),
				date_create('1998-06-04 09:00:00'),
				date_create('1998-06-11 09:00:00'),
				date_create('1998-06-18 09:00:00'),
				date_create('1998-06-25 09:00:00'),
				date_create('1998-07-02 09:00:00'),
				date_create('1998-07-09 09:00:00'),
				date_create('1998-07-16 09:00:00'),
				date_create('1998-07-23 09:00:00'),
				date_create('1998-07-30 09:00:00'),
				date_create('1998-08-06 09:00:00'),
				date_create('1998-08-13 09:00:00'),
				date_create('1998-08-20 09:00:00'),
				date_create('1998-08-27 09:00:00'))
			),
			// Every Friday the 13th, 4 occurrences.
			array(
				array('freq' => 'yearly', 'byday' => 'FR', 'bymonthday' => 13, 'count' => 4, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1998-02-13 09:00:00'),
				date_create('1998-03-13 09:00:00'),
				date_create('1998-11-13 09:00:00'),
				date_create('1999-08-13 09:00:00'))
			),
			// The first Saturday that follows the first Sunday of the month
			array(
				array('freq' => 'monthly', 'byday' => 'SA', 'bymonthday' => array(7,8,9,10,11,12,13), 'count' => 10, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-13 09:00:00'),
				date_create('1997-10-11 09:00:00'),
				date_create('1997-11-08 09:00:00'),
				date_create('1997-12-13 09:00:00'),
				date_create('1998-01-10 09:00:00'),
				date_create('1998-02-07 09:00:00'),
				date_create('1998-03-07 09:00:00'),
				date_create('1998-04-11 09:00:00'),
				date_create('1998-05-09 09:00:00'),
				date_create('1998-06-13 09:00:00'))
			),
			// Every four years, the first Tuesday after a Monday in November, 3 occurrences (U.S. Presidential Election day):
			array(
				array('freq' => 'yearly', 'interval' => 4, 'bymonth' => 11, 'byday' => 'TU', 'bymonthday' => array(2,3,4,5,6,7,8), 'count' => 3, 'dtstart' => '1996-11-05 09:00:00'),
				array(date_create('1996-11-05 09:00:00'),
				date_create('2000-11-07 09:00:00'),
				date_create('2004-11-02 09:00:00'))
			),
			// The 3rd instance into the month of one of Tuesday, Wednesday or Thursday, for the next 3 months:
			array(
				array('freq' => 'monthly', 'byday' => 'TU,WE,TH', 'bysetpos' => 3, 'count' => 3, 'dtstart' => '1997-09-04 09:00:00'),
				array(date_create('1997-09-04 09:00:00'),
				date_create('1997-10-07 09:00:00'),
				date_create('1997-11-06 09:00:00'))
			),
			// The 2nd to last weekday of the month, 3 occurrences.
			array(
				array('freq' => 'monthly', 'byday' => 'MO,TU,WE,TH,FR', 'bysetpos' => -2, 'count' => 3, 'dtstart' => '1997-09-29 09:00:00'),
				array(date_create('1997-09-29 09:00:00'),
				date_create('1997-10-30 09:00:00'),
				date_create('1997-11-27 09:00:00'))
			),
			// Every 3 hours from 9:00 AM to 5:00 PM on a specific day.
			array(
				array('freq' => 'hourly', 'interval' => 3, 'dtstart' => '1997-09-29 09:00:00', 'until' => '1997-09-29 17:00:00'),
				array(date_create('1997-09-29 09:00:00'),
				date_create('1997-09-29 12:00:00'),
				date_create('1997-09-29 15:00:00'))
			),
			// Every 15 minutes for 6 occurrences.
			array(
				array('freq' => 'MINUTELY', 'interval' => 15, 'count' => 6, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-02 09:15:00'),
				date_create('1997-09-02 09:30:00'),
				date_create('1997-09-02 09:45:00'),
				date_create('1997-09-02 10:00:00'),
				date_create('1997-09-02 10:15:00'))
			),
			// Every hour and a half for 4 occurrences.
			array(
				array('freq' => 'MINUTELY', 'interval' => 90, 'count' => 4, 'dtstart' => date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney'))),
				array(date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 10:30:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 12:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 13:30:00',new DateTimeZone('Australia/Sydney')))
			),
			// Every 20 minutes from 9:00 AM to 4:40 PM for two days.
			array(
				array('freq' => 'MINUTELY', 'interval' => 20, 'count' => 48,
					'byhour' => range(9,16), 'byminute' => '0,20,40',
					'dtstart' => date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney'))), array(
				date_create('1997-09-02 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 09:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 09:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 10:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 10:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 10:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 11:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 11:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 11:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 12:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 12:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 12:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 13:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 13:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 13:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 14:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 14:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 14:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 15:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 15:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 15:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 16:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 16:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-02 16:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 09:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 09:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 09:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 10:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 10:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 10:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 11:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 11:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 11:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 12:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 12:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 12:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 13:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 13:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 13:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 14:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 14:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 14:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 15:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 15:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 15:40:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 16:00:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 16:20:00',new DateTimeZone('Australia/Sydney')),
				date_create('1997-09-03 16:40:00',new DateTimeZone('Australia/Sydney')))
			),
			// An example where the days generated makes a difference because of wkst.
			array(array('freq' => 'WEEKLY', 'interval' => 2, 'count' => 4,
				'byday' => 'TU,SU', 'WKST' => 'MO', 'dtstart' => '1997-08-05 09:00:00'),array(
				date_create('1997-08-05 09:00:00'),
				date_create('1997-08-10 09:00:00'),
				date_create('1997-08-19 09:00:00'),
				date_create('1997-08-24 09:00:00'))
			),
			array(array('freq' => 'WEEKLY', 'interval' => 2, 'count' => 4,
				'byday' => 'TU,SU', 'WKST' => 'SU', 'dtstart' => '1997-08-05 09:00:00'),array(
				date_create('1997-08-05 09:00:00'),
				date_create('1997-08-17 09:00:00'),
				date_create('1997-08-19 09:00:00'),
				date_create('1997-08-31 09:00:00'))
			),

		);
	}

	/**
	 * @dataProvider rfcExamples
	 */
	public function testRfcExamples($rule, $occurrences)
	{
		$rule = new RRule($rule);
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * Rules that generate no occurrence, because of a bad combination of BYXXX parts
	 * This tests are here to ensure that the lib will not go into an infinite loop.
	 */
	public function rulesWithoutOccurrences()
	{
		return array(
			// Every year on February and on the week number 50 (impossible)
			array(array(
				'freq' => 'yearly',
				'interval' => 1,
				'bymonth' => 2,
				'byweekno' => 50,
				'dtstart' => '1997-02-02 09:00:00',
				'count' => 1
			)),
			// Every 2 months, on odd months, starting a even month (nope)
			array(array(
				'freq' => 'monthly',
				'interval' => 2,
				'bymonth' => '1,3,5,7,9,11',
				'dtstart' => '1997-02-02 09:00:00',
				'count' => 1
			)),

			// haven't found a weekly rule with no occurrence yet

			// every 7 days, monday, starting a wednesday (still nope)
			array(array(
				'freq' => 'daily',
				'interval' => 7,
				'byday' => 'MO',
				'dtstart' => '2015-07-01 09:00:00',
				'count' => 1
			)),
			// every 4 hours, on odd hours, starting an even hour (nein)
			array(array(
				'freq' => 'hourly',
				'interval' => 4,
				'byhour' => '7, 11, 15, 19',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			)),
			array(array(
				'freq' => 'minutely',
				'interval' => 12,
				'byminute' => '10, 11, 25, 39, 50',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			)),
			array(array(
				'freq' => 'minutely',
				'interval' => 120,
				'byminute' => '10, 12, 14, 16',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			)),
			array(array(
				'freq' => 'secondly',
				'interval' => 10,
				'bysecond' => '2, 15, 37, 42, 59',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			)),
			array(array(
				'freq' => 'secondly',
				'interval' => 360,
				'bysecond' => '10, 28, 49',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			)),
			array(array(
				'freq' => 'secondly',
				'interval' => 43200,
				'bysecond' => '2, 10, 18, 23',
				'dtstart'=> '1997-09-02 09:00:00',
				'count' => 1
			))
		);
	}
	/**
	 * @dataProvider rulesWithoutOccurrences
	 */
	public function testRulesWithoutOccurrences($rule)
	{
		$rule = new RRule($rule);
		$occurrences = $rule->getOccurrences();
		$this->assertEmpty($rule->getOccurrences(), 'This should be empty : '.json_encode($occurrences));
	}

	/**
	 * Just some more random rules found here and there, with some edges cases.
	 * Some of them might not bring any additional value to the tests to be honest, but
	 * it's good to test them anyway.
	 */
	public function variousRules()
	{
		return array(
			array(
				array('freq' => 'daily', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01'),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'weekly', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01'),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'daily', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01', 'bysetpos' => 1),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'weekly', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01', 'bysetpos' => 1),
				array(date_create('2007-01-02'), date_create('2007-01-09'), date_create('2007-01-16'))
			),
			// The week number 1 may be in the last year.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 1, 'byday' => 'MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-12-29 09:00:00'),
				date_create('1999-01-04 09:00:00'),
				date_create('2000-01-03 09:00:00'))
			),
			// And the week numbers greater than 51 may be in the next year.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 52, 'byday' => 'SU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-12-28 09:00:00'),
				date_create('1998-12-27 09:00:00'),
				date_create('2000-01-02 09:00:00'))
			),
			// Only some years have week number 53
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 53, 'byday' => 'MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1998-12-28 09:00:00'),
				date_create('2004-12-27 09:00:00'),
				date_create('2009-12-28 09:00:00'))
			),
			// test that occurrences are returned in chronogical order, even when the BYXXX are not in order
			array(
				array('freq' => 'yearly', 'bymonth' => '2,1', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2016-01-01 09:00:00'),
				date_create('2016-02-01 09:00:00'),
				date_create('2017-01-01 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'byweekno' => '30,50,40', 'byday' => 'MO', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-20 09:00:00'),
				date_create('2015-09-28 09:00:00'),
				date_create('2015-12-07 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'byyearday' => '3,2,1', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2016-01-01 09:00:00'),
				date_create('2016-01-02 09:00:00'),
				date_create('2016-01-03 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'bymonthday' => '31,30', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-30 09:00:00'),
				date_create('2015-07-31 09:00:00'),
				date_create('2015-08-30 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'byday' => 'TU,MO', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-06 09:00:00'),
				date_create('2015-07-07 09:00:00'),
				date_create('2015-07-13 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'byhour' => '9,8', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-01 09:00:00'),
				date_create('2016-07-01 08:00:00'),
				date_create('2016-07-01 09:00:00'))
			),
			array(
				array('freq' => 'yearly', 'byminute' => '30,15', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-01 09:15:00'),
				date_create('2015-07-01 09:30:00'),
				date_create('2016-07-01 09:15:00'))
			),
			array(
				array('freq' => 'yearly', 'bysecond' => '30,15', 'count' => 3, 'dtstart' => '2015-07-01 09:00:00'),
				array(date_create('2015-07-01 09:00:15'),
				date_create('2015-07-01 09:00:30'),
				date_create('2016-07-01 09:00:15'))
			),
			// every 52 weeks, in November, starting in July (will happen in 2185 - to test year 2038 problem)
			array(array(
				'freq' => 'weekly',
				'interval' => 52,
				'bymonth' => 11,
				'dtstart' => '2015-07-01 09:00:00',
				'count' => 1), array(
				date_create('2185-11-30 09:00:00')
			)),
		);
	}

	/**
	 * @dataProvider variousRules
	 */
	public function testVariousRules($rule, $occurrences)
	{
		$rule = new RRule($rule);
		$this->assertEquals($occurrences, $rule->getOccurrences());
		$this->assertEquals($occurrences, $rule->getOccurrences(), 'Cached version');
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in cached version');
		}
		$rule->clearCache();
		foreach ($occurrences as $date) {
			$this->assertTrue($rule->occursAt($date), $date->format('r').' in uncached version');
		}
		$rule->clearCache();
		for ($i = 0; $i < count($occurrences); $i++) {
			$this->assertEquals($rule[$i], $occurrences[$i], 'array access uncached');
		}
	}

	/**
	 * Test that occursAt doesn't return false positives
	 */
	public function notOccurrences()
	{
		return array(
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02'),
				array('1999-09-01','1999-09-02 12:00:00','1999-09-03')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'UNTIL' => '2000-09-02'),
				array('2001-09-02', '2000-09-02 12:00:00')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'COUNT' => 3),
				array('2010-09-02')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'INTERVAL' => 2),
				array('2000-09-02', '2002-09-02')
			),
			'byyearday' => [
				['FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'byyearday' => 1],
				['1999-09-02']
			],
			'byweekno' => [
				['FREQ' => 'YEARLY', 'DTSTART' => '2015-07-01', 'BYWEEKNO' => 1],
				['2015-07-01']
			],
			array(
				array('FREQ' => 'MONTHLY', 'DTSTART' => '1999-09-02', 'INTERVAL' => 2),
				array('1999-10-02', '1999-12-02')
			),
			'bymonth' => [
				['FREQ' => 'MONTHLY', 'DTSTART' => '1999-09-02', 'bymonth' => 1],
				['1999-10-02', '1999-12-02']
			],
			array(
				array('FREQ' => 'WEEKLY', 'DTSTART' => '2015-07-01', 'INTERVAL' => 2),
				array('2015-07-02', '2015-07-07 23:59:59', '2015-07-08 00:00:01', '2015-07-08')
			),
			array(
				array('FREQ' => 'DAILY', 'DTSTART' => '2015-07-01', 'INTERVAL' => 2),
				array('2015-07-02', '2015-07-02 23:59:59', '2015-07-03 00:00:01')
			),
			array(
				array('freq' => 'hourly', 'dtstart' => '1999-09-02 09:00:00', 'INTERVAL' => 2),
				array('1999-09-02 10:00:00', '1999-09-02 09:01:01','1999-09-02 12:00:00')
			),
			array(
				array('freq' => 'hourly', 'dtstart' => '1999-09-02 09:00:00', 'INTERVAL' => 5),
				array('1999-09-03 09:00:00')
			),
			array(
				array('freq' => 'minutely', 'dtstart' => '1999-09-02 09:00:00', 'INTERVAL' => 5),
				array('1999-09-02 09:01:00')
			),
			array(
				array('freq' => 'secondly', 'dtstart' => '1999-09-02 09:00:00', 'INTERVAL' => 5),
				array('1999-09-02 09:00:01')
			),
		);
	}

	/**
	 * @dataProvider notOccurrences
	 */
	public function testNotOccurrences($rule, $not_occurrences)
	{
		$rule = new RRule($rule);
		foreach ($not_occurrences as $date) {
			$this->assertFalse($rule->occursAt($date), "Rule must not match $date");
		}
	}

	public function rulesBeyondMaxCycles()
	{
		return [
			['yearly' => 'YEARLY', 30],
			['monthly' => 'MONTHLY', 400],
			['weekly' => 'WEEKLY', 1500],
			['daily' => 'DAILY', 11000],
			['hourly' => 'HOURLY', 30],
			['minutely' => 'MINUTELY', 1500]
		];
	}

	/**
	 * @dataProvider rulesBeyondMaxCycles
	 */
	public function testMaxCyclesDoesntKickInIfTheRuleProduceOccurrences($frequency, $count)
	{
		// see https://github.com/rlanvin/php-rrule/issues/78
		$rrule = new RRule(['FREQ' => $frequency, 'COUNT' => $count]);
		$this->assertEquals($count, $rrule->count());
	}

///////////////////////////////////////////////////////////////////////////////
// GetOccurrences

	public function testGetOccurrences()
	{
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));

		$this->assertCount(1, $rrule->getOccurrences(1));
		$this->assertEquals(array(date_create('2017-01-01')), $rrule->getOccurrences(1));
		$this->assertCount(5, $rrule->getOccurrences(5));
		$this->assertEquals(array(
			date_create('2017-01-01'),date_create('2017-01-02'),date_create('2017-01-03'),
			date_create('2017-01-04'),date_create('2017-01-05')
		), $rrule->getOccurrences(5));
	}

	public function testGetOccurrencesThrowsLogicException()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage("Cannot get all occurrences of an infinite recurrence rule.");
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));
		$rrule->getOccurrences();
	}

	public function testGetOccurrencesNegativeLimit()
	{
		$this->expectException(\InvalidArgumentException::class);
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));
		$rrule->getOccurrences(-1);
	}

	public function occurrencesBetween()
	{
		return [
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', null, 1, [date_create('2017-01-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-02-01', '2017-12-31', 1, [date_create('2017-02-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', null, 5, [
				date_create('2017-01-01'),
				date_create('2017-01-02'),
				date_create('2017-01-03'),
				date_create('2017-01-04'),
				date_create('2017-01-05'),
			]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', '2017-01-05', null, [
				date_create('2017-01-01'),
				date_create('2017-01-02'),
				date_create('2017-01-03'),
				date_create('2017-01-04'),
				date_create('2017-01-05'),
			]],
		];
	}

	/**
	 * @dataProvider occurrencesBetween
	 */
	public function testGetOccurrencesBetween($rule, $begin, $end, $limit, $expected)
	{
		$rrule = new RRule($rule);

		$this->assertEquals($expected, $rrule->getOccurrencesBetween($begin, $end, $limit));
	}

	public function testGetOccurrencesBetweenThrowsLogicException()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage("Cannot get all occurrences of an infinite recurrence rule.");
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));
		$rrule->getOccurrencesBetween('2017-01-01', null);
	}

	public function testGetOccurrencesBetweenNegativeLimit()
	{
		$this->expectException(\InvalidArgumentException::class);
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));
		$rrule->getOccurrencesBetween('2017-01-01', '2018-01-01', -1);
	}

	public function occurrencesAfter()
	{
		return [
			["DTSTART:20170101\nRRULE:FREQ=DAILY;UNTIL=20170103", '2017-01-01', false, null, [date_create('2017-01-02'), date_create('2017-01-03')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;UNTIL=20170103", '2017-01-01', true, null, [date_create('2017-01-01'), date_create('2017-01-02'), date_create('2017-01-03')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-02-01', false, 2, [date_create('2017-02-02'),date_create('2017-02-03')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-02-01', true, 2, [date_create('2017-02-01'),date_create('2017-02-02')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-02', true, 2, [date_create('2017-01-03'),date_create('2017-01-05')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-02', false, 2, [date_create('2017-01-03'),date_create('2017-01-05')]],
		];
	}

	/**
	 * @dataProvider occurrencesAfter
	 */
	public function testGetOccurrencesAfter($rrule, $date, $inclusive, $limit, $expected)
	{
		$rrule = new RRule($rrule);
		$occurrences = $rrule->getOccurrencesAfter($date, $inclusive, $limit);
		$this->assertEquals($expected, $occurrences);
	}

	public function testGetOccurrencesAfterThrowsLogicException()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage("Cannot get all occurrences of an infinite recurrence rule.");
		$rrule = new RRule(array(
			'FREQ' => 'DAILY',
			'DTSTART' => '2017-01-01'
		));
		$rrule->getOccurrencesAfter('2017-01-01');
	}

	public function occurrencesBefore()
	{
		return [
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-02-01', true, 2, [date_create('2017-01-31'),date_create('2017-02-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-02-01', false, 2, [date_create('2017-01-30'),date_create('2017-01-31')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-02', false, null, [date_create('2017-01-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-02', false, 5, [date_create('2017-01-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-04', true, 2, [date_create('2017-01-01'),date_create('2017-01-03')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-04', false, 2, [date_create('2017-01-01'),date_create('2017-01-03')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-02', false, null, [date_create('2017-01-01')]],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-02', false, 5, [date_create('2017-01-01')]],
		];
	}
	/**
	 * @dataProvider occurrencesBefore
	 */
	public function testGetOccurrencesBefore($rrule, $date, $inclusive, $limit, $expected)
	{
		$rrule = new RRule($rrule);
		$occurrences = $rrule->getOccurrencesBefore($date, $inclusive, $limit);
		$this->assertEquals($expected, $occurrences);
	}

	public function nthOccurrences()
	{
		return [
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', 0, date_create('2017-01-01')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', 1, date_create('2017-01-02')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-01', 2, date_create('2017-01-05')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-02', 0, null],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-01', -1, null],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-10', -1, date_create('2017-01-09')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY", '2017-01-10', -2, date_create('2017-01-08')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-11', -2, date_create('2017-01-07')],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;INTERVAL=2", '2017-01-10', -2, date_create('2017-01-07')],

			["DTSTART:20170101\nRRULE:FREQ=DAILY;COUNT=2", '2017-01-01', 3, null],
			["DTSTART:20170101\nRRULE:FREQ=DAILY;UNTIL=20170102", '2017-01-01', 3, null],

		];
	}

	/**
	 * @dataProvider nthOccurrences
	 */
	public function testGetNthOccurrenceFrom($rrule, $date, $index, $result)
	{
		$rrule = new RRule($rrule);
		$occurrence = $rrule->getNthOccurrenceFrom($date, $index);
		$this->assertEquals($result, $occurrence);
	}

	public function testGetNthOccurrenceFromInvalidIndex()
	{
		$rrule = new RRule(['FREQ' => 'DAILY']);
		$this->expectException(\InvalidArgumentException::class);
		$rrule->getNthOccurrenceFrom(date_create('2017-01-09'), []);
	}

	public function testGetNthOccurrenceBeforeInvalidIndex()
	{
		$rrule = new RRule(['FREQ' => 'DAILY']);
		$this->expectException(\InvalidArgumentException::class);
		$rrule->getNthOccurrenceBefore(date_create('2017-01-09'), -1);
	}

	public function testGetNthOccurrenceAfterInvalidIndex()
	{
		$rrule = new RRule(['FREQ' => 'DAILY']);
		$this->expectException(\InvalidArgumentException::class);
		$rrule->getNthOccurrenceAfter(date_create('2017-01-09'), -1);
	}

///////////////////////////////////////////////////////////////////////////////
// RFC Strings

	public function rfcStrings()
	{
		return array(
			// full RFC string
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=HOURLY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1;BYHOUR=1',
				null // todo
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=DAILY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1',
				null // todo
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=DAILY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1;BYHOUR=12;BYMINUTE=15,30',
				null
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=WEEKLY;INTERVAL=2;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR',
				null // todo
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=WEEKLY;INTERVAL=2;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR',
				null // todo
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1',
				array()
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTHDAY=1,2,5,31,-1,-3,-15',
				array(
					date_create('1997-09-01 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-09-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-01 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-17 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-29 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-31 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-11-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-11-28 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-12-01 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-12-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-12-17 09:00:00', new DateTimeZone('America/New_York'))
				)
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTHDAY=1,2,5,31,-1,-3,-15;BYSETPOS=-1',
				array(
					date_create('1997-09-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-31 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-11-28 09:00:00', new DateTimeZone('America/New_York')),
				)
			),
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTHDAY=1,2,5,31,-1,-3,-15;BYSETPOS=-1,1',
				array(
					date_create('1997-09-01 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-09-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-01 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-10-31 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-11-05 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-11-28 09:00:00', new DateTimeZone('America/New_York')),
					date_create('1997-12-01 09:00:00', new DateTimeZone('America/New_York'))
				)
			),
			array(' DTSTART;TZID=America/New_York:19970512T090000
			RRULE:FREQ=YEARLY;BYWEEKNO=20,30,40;BYDAY=MO',
				null
			),
			array('DTSTART;TZID=America/New_York:19970512T090000
			RRULE:FREQ=YEARLY;BYYEARDAY=1,-1,10,-50;BYDAY=MO',
				null
			),
			array('DTSTART:19970512T090000Z
			RRULE:FREQ=YEARLY',
				null
			),
			array('DTSTART:19970512T090000
			RRULE:FREQ=YEARLY',
				null
			),
			array('DTSTART:19970512
			RRULE:FREQ=YEARLY',
				null
			),

			// case insensitive
			// array("dtstart:19970902T090000\nrrule:freq=yearly;count=3",
			// 	array(date_create('1997-09-02 09:00:00'),date_create('1998-09-02 09:00:00'),date_create('1999-09-02 09:00:00'))
			// ),

			// empty lines
			array("\nDTSTART:19970512\nRRULE:FREQ=YEARLY;COUNT=3\n\n",
				array(date_create('1997-05-12'),date_create('1998-05-12'),date_create('1999-05-12'))
			),
			// CRLF
			array("\r\nDTSTART:19970512\r\nRRULE:FREQ=YEARLY;COUNT=3\r\n\r\n",
				array(date_create('1997-05-12'),date_create('1998-05-12'),date_create('1999-05-12'))
			),

			// no DTSTART
			array("RRULE:FREQ=YEARLY;COUNT=3",
				null
			),
			array("RRULE:FREQ=YEARLY;UNTIL=20170202",
				null
			),
			array("RRULE:FREQ=YEARLY;UNTIL=20170202T090000",
				null
			),
			array("RRULE:FREQ=YEARLY;UNTIL=20170202T090000Z",
				null
			),

			// just the RRULE property
			array('FREQ=DAILY',
				null
			),
			array("FREQ=YEARLY;UNTIL=20170202",
				null
			),
			array("FREQ=YEARLY;UNTIL=20170202T090000",
				null
			),
			array("FREQ=YEARLY;UNTIL=20170202T090000Z",
				null
			),

			// non-standard timezones
			'Windows timezone' => [
				'DTSTART;TZID=W. Europe Standard Time:19970901T090000
			RRULE:FREQ=DAILY;COUNT=3',
				[
					date_create('1997-09-01 09:00:00', new DateTimeZone('Europe/Berlin')),
					date_create('1997-09-02 09:00:00', new DateTimeZone('Europe/Berlin')),
					date_create('1997-09-03 09:00:00', new DateTimeZone('Europe/Berlin')),
				]
			],
		);
	}

	/**
	 * @dataProvider rfcStrings
	 */
	public function testRfcStringParser($str, $occurrences)
	{
		$rule = new RRule($str);

		// test that parsing the string produces the same result
		// as generating the string from a rule
		$this->assertEquals($rule, new RRule($rule->rfcString()));

		if ($occurrences) {
			$this->assertEquals($occurrences, $rule->getOccurrences());
		}
	}



	public function testRfcStringParserWithDtStart()
	{
		$rrule = new RRule('RRULE:FREQ=YEARLY');
		$this->assertEquals(date_create()->format('Y-m-d'), $rrule[0]->format('Y-m-d'));

		$rrule = new RRule('RRULE:FREQ=YEARLY', date_create('2017-01-01'));
		$this->assertEquals('2017-01-01', $rrule[0]->format('Y-m-d'));

		$rrule = new RRule('RRULE:FREQ=YEARLY', '2017-01-01');
		$this->assertEquals('2017-01-01', $rrule[0]->format('Y-m-d'));
	}

	public function testRfcStringParserWithMultipleDtStart()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Too many DTSTART properties (there can be only one)");

		$rrule = new RRule("DTSTART:19970512\nRRULE:FREQ=YEARLY", date_create('2017-01-01'));
	}

	/**
	 * @see https://github.com/rlanvin/php-rrule/issues/25
	 */
	public function quirkyRfcStrings()
	{
		return array(
			array('DTSTART=20160202T000000Z;FREQ=DAILY;UNTIL=20160205T000000Z',
				array(
					date_create('2016-02-02', new DateTimeZone('UTC')),
					date_create('2016-02-03', new DateTimeZone('UTC')),
					date_create('2016-02-04', new DateTimeZone('UTC')),
					date_create('2016-02-05', new DateTimeZone('UTC'))
				)
			),
			array('RRULE:DTSTART=20160202T000000Z;FREQ=DAILY;UNTIL=20160205T000000Z',
				array(
					date_create('2016-02-02', new DateTimeZone('UTC')),
					date_create('2016-02-03', new DateTimeZone('UTC')),
					date_create('2016-02-04', new DateTimeZone('UTC')),
					date_create('2016-02-05', new DateTimeZone('UTC'))
				)
			)
		);
	}

	/**
	 * @dataProvider quirkyRfcStrings
	 */
	public function testQuirkyRfcStringsParserNotice($str,$occurrences)
	{
		$this->expectException(\PHPUnit\Framework\Error\Notice::class);
		$rule = new RRule($str);
	}

	/**
	 * @dataProvider quirkyRfcStrings
	 */
	public function testQuirkyRfcStringsParser($str,$occurrences)
	{
		$rule = @ new RRule($str);

		if ($occurrences) {
			$this->assertEquals($occurrences, $rule->getOccurrences(), '', 1);
		}
	}

	public function invalidRfcStrings()
	{
		return array(
			// plain invalid strings
			array('foobar'),
			array('blah=blah=blah'),

			// test invalid date formats
			array('DTSTART:2006-06-24
			RRULE:FREQ=DAILY'),
			array('DTSTART:2006-06-24 12:00:00
			RRULE:FREQ=DAILY'),
			array('DTSTART:20060624
			RRULE:FREQ=DAILY;UNTIL=2006-06-24'),

			// multiple dtstart
			array('DTSTART:20060624
			RRULE:DTSTART=20060630;FREQ=DAILY;UNTIL=20060624'),

			// test combinations of DTSTART and UNTIL which are invalid
			array('DTSTART;TZID=Australia/Sydney:20160624
			RRULE:FREQ=DAILY;INTERVAL=1;UNTIL=20160628'),
			array('DTSTART;TZID=America/New_York:19970512T090000Z
			RRULE:FREQ=YEARLY'),
			array('DTSTART;TZID=America/New_York:19970512T090000
			RRULE:FREQ=YEARLY;UNTIL=19970512'),
			array('DTSTART;TZID=America/New_York:19970512T090000
			RRULE:FREQ=YEARLY;UNTIL=19970512T090000'),
			array('DTSTART:19970512T090000
			RRULE:FREQ=YEARLY;UNTIL=19970512'),
			array('DTSTART:19970512T090000Z
			RRULE:FREQ=YEARLY;UNTIL=19970512'),
			array('DTSTART:19970512
			RRULE:FREQ=YEARLY;UNTIL=19970512T090000'),
			array('DTSTART:19970512
			RRULE:FREQ=YEARLY;UNTIL=19970512T090000Z'),

			// missing RRULE
			array("DTSTART:20060624\nFREQ=DAILY"),

			// multiple RRULE or DTSTART
			array("DTSTART:20060624\nRRULE:FREQ=DAILY\nRRULE:FREQ=YEARLY"),
			array("DTSTART:20060624\nDTSTART:20060624\nRRULE:FREQ=YEARLY"),

			// properties for Rset
			array("DTSTART:20060624\nRRULE:FREQ=DAILY\nEXRULE:FREQ=YEARLY"),
		);
	}

	/**
	 * @dataProvider invalidRfcStrings
	 */
	public function testInvalidRfcStrings($str)
	{
		$this->expectException(\InvalidArgumentException::class);
		$rule = new RRule($str);
	}

	public function testRfcStringWithUTC()
	{
		$rule = new RRule('DTSTART:19970512T090000Z
			RRULE:FREQ=YEARLY');
		$this->assertEquals("DTSTART:19970512T090000Z\nRRULE:FREQ=YEARLY", $rule->rfcString());
	}

	/**
	 * @see https://github.com/rlanvin/php-rrule/issues/15
	 */
	public function testRfcStsringsWithTimestamp()
	{
		$rrule = new RRule(array(
			"freq" => "WEEKLY",
			"dtstart" => 1470323171,
			"interval" => 1
		));

		$str = $rrule->rfcString();
		$new_rrule = new RRule($str);
		$this->assertInstanceOf('RRule\RRule', $new_rrule);
	}

	public function testUnsupportedTimezoneConvertedToUtc()
	{
		$date = new DateTime('2016-07-08 12:00:00', new DateTimeZone('+06:00'));
		$rrule = new RRule(array(
			"freq" => "WEEKLY",
			"dtstart" => $date,
			"interval" => 1
		));

		$str = $rrule->rfcString();
		$this->assertTrue(strpos($str, '20160708T060000Z')!== false);
		$new_rrule = new RRule($str);
	}

	public function rfcStringsWithoutTimezone()
	{
		return array(
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=DAILY",
				"DTSTART:19970901T090000\nRRULE:FREQ=DAILY",
			),
			array(
				"DTSTART;TZID=Europe/Paris:19970901T090000\nRRULE:FREQ=DAILY;UNTIL=19970902T070000Z",
				"DTSTART:19970901T090000\nRRULE:FREQ=DAILY;UNTIL=19970902T090000",
			),
		);
	}

	/**
	 * @dataProvider rfcStringsWithoutTimezone
	 */
	public function testRfcStringWithoutTimezone($str, $expected_str)
	{
		$rule = new RRule($str);
		$this->assertEquals($expected_str, $rule->rfcString(false));
	}

	public function rfcStringsGenerated()
	{
		return array(
			array(
				array(
					'FREQ' => RRule::YEARLY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=YEARLY"
			),
			array(
				array(
					'FREQ' => RRule::MONTHLY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=MONTHLY"
			),
			array(
				array(
					'FREQ' => RRule::WEEKLY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=WEEKLY"
			),
			array(
				array(
					'FREQ' => RRule::DAILY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=DAILY"
			),
			array(
				array(
					'FREQ' => RRule::HOURLY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=HOURLY"
			),
			array(
				array(
					'FREQ' => RRule::MINUTELY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=MINUTELY"
			),
			array(
				array(
					'FREQ' => RRule::SECONDLY,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=SECONDLY"
			),
			array(
				array(
					'FREQ' => RRule::SECONDLY,
					'BYMINUTE' => 0,
					'BYHOUR' => 0,
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=SECONDLY;BYMINUTE=0;BYHOUR=0"
			),
			'with a value as an array' => [
				array(
					'FREQ' => RRule::SECONDLY,
					'BYMINUTE' => 0,
					'BYHOUR' => [0,1],
					'DTSTART' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
				),
				"DTSTART;TZID=Australia/Sydney:20150701T090000\nRRULE:FREQ=SECONDLY;BYMINUTE=0;BYHOUR=0,1"
			],
		);
	}

	/**
	 * @dataProvider rfcStringsGenerated
	 */
	public function testRfcStringsGenerated($params, $expected_str)
	{
		$rule = new RRule($params);
		$this->assertEquals($expected_str, $rule->rfcString());
	}

	public function testMagicStringMethod()
	{
		$rule = new RRule('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=HOURLY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1;BYHOUR=1');

		$this->assertEquals($rule->rfcString(), (string) $rule);
	}

///////////////////////////////////////////////////////////////////////////////
// RFC Factory method

	public function rfcStringsForFactory()
	{
		return array(
			array('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=HOURLY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1;BYHOUR=1',
				'\RRule\RRule'
			),
			array('DTSTART;TZID=America/New_York:19970512T090000
			RRULE:FREQ=YEARLY;BYYEARDAY=1,-1,10,-50;BYDAY=MO',
				'\RRule\RRule'
			),
			array('DTSTART:19970512T090000Z
			RRULE:FREQ=YEARLY',
				'\RRule\RRule'
			),
			array('DTSTART:19970512T090000
			RRULE:FREQ=YEARLY',
				'\RRule\RRule'
			),
			array('DTSTART:19970512
			RRULE:FREQ=YEARLY',
				'\RRule\RRule'
			),
			// empty lines
			array("\nDTSTART:19970512\nRRULE:FREQ=YEARLY;COUNT=3\n\n",
				'\RRule\RRule'
			),
			'no DTSTART' => [
				"RRULE:FREQ=YEARLY;COUNT=3",
				'\RRule\RRule'
			],
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=DAILY\nEXRULE:FREQ=YEARLY\nEXDATE;TZID=America/New_York:19970902T090000",
				'\RRule\RSet'
			),
			'no rrule' => [
				'EXRULE:FREQ=DAILY;COUNT=3',
				\RRule\RRule::class
			],
			'lowercase rrule' => [
				"rrule:freq=yearly;count=3",
				"\RRule\RRule"
			],
			'lowercase rset with 2 rrules' => [
				"rrule:freq=yearly;count=3\nrrule:freq=monthly",
				"\RRule\RSet"
			]
		);
	}

	/**
	 * @dataProvider rfcStringsForFactory
	 */
	public function testCreateFromRfcString($string, $expected_class)
	{
		$object = RRule::createFromRfcString($string);
		$this->assertInstanceOf($expected_class, $object);
	}

	/**
	 * @dataProvider rfcStringsForFactory
	 */
	public function testCreateFromRfcStringForceRSet($string)
	{
		$object = RRule::createFromRfcString($string, true);
		$this->assertInstanceOf('\RRule\RSet', $object);
	}

	public function testCreateFromRfcStringDoesntChangeCase()
	{
		$str = "DTSTART;TZID=Europe/Paris:20200929T000000\nRRULE:FREQ=DAILY;BYSECOND=0;BYMINUTE=0;BYHOUR=9";
		$rule = RRule::createFromRfcString($str);
		$this->assertEquals($str, $rule->rfcString());
	}

///////////////////////////////////////////////////////////////////////////////
// Timezone

	public function testTimezoneIsKeptIdentical()
	{
		$rrule = new RRule(array(
			'freq' => 'yearly',
			'bymonthday' => '31,30',
			'count' => 3,
			'dtstart' => date_create('2015-07-01 09:00:00')
		));

		$this->assertEquals(date_create('2015-07-30 09:00:00'), $rrule[0]);

		$rrule = new RRule(array(
			'freq' => 'yearly',
			'bymonthday' => '31,30',
			'count' => 3,
			'dtstart' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
		));

		$this->assertEquals(date_create('2015-07-30 09:00:00', new DateTimeZone('Australia/Sydney')), $rrule[0]);

		$rrule = new RRule(array(
			'freq' => 'yearly',
			'bymonthday' => '31,30',
			'count' => 3,
			'dtstart' => date_create('2015-07-01 09:00:00', new DateTimeZone('Europe/Helsinki'))
		));

		$this->assertEquals(date_create('2015-07-30 09:00:00', new DateTimeZone('Europe/Helsinki')), $rrule[0]);

		// using a rfc string
		$rrule = new RRule('DTSTART;TZID=America/New_York:19970901T090000
			RRULE:FREQ=DAILY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR');

		$this->assertEquals(date_create('1997-09-01 09:00:00', new DateTimeZone('America/New_York')), $rrule[0]);
	}

	public function testOccursAtTakeTimezoneIntoAccount()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 365,
			'dtstart' => date_create('2015-07-01 09:00:00')
		));
		$this->assertTrue($rrule->occursAt('2015-07-02 09:00:00'), 'When timezone is not specified, it takes the default timezone');

		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 365,
			'dtstart' => date_create('2015-07-01 09:00:00', new DateTimeZone('Australia/Sydney'))
		));
		$this->assertTrue($rrule->occursAt(date_create('2015-07-02 09:00:00',new DateTimeZone('Australia/Sydney'))));
		$this->assertTrue($rrule->occursAt(date_create('2015-07-01 23:00:00',new DateTimeZone('UTC'))), 'Timezone is converted for comparison (cached)');
		$rrule->clearCache();
		$this->assertTrue($rrule->occursAt(date_create('2015-07-01 23:00:00',new DateTimeZone('UTC'))), 'Timezone is converted for comparison (uncached)');

		$rrule->clearCache();
		$this->assertFalse($rrule->occursAt('2015-07-02 09:00:00'), 'When passed a string, default timezone is used for creating the DateTime');

		$rrule->clearCache();
		$this->assertTrue($rrule->occursAt('Wed, 01 Jul 2015 09:00:00 +1000'), 'When passed a string with timezone, timezone is kept (uncached)');
		$this->assertTrue($rrule->occursAt('Wed, 01 Jul 2015 09:00:00 +1000'), 'When passed a string with timezone, timezone is kept (cached)');

		$rrule->clearCache();
		$this->assertTrue($rrule->occursAt('2015-07-01T09:00:00+10:00'), 'When passed a string with timezone, timezone is kept (uncached)');
		$this->assertTrue($rrule->occursAt('2015-07-01T09:00:00+10:00'), 'When passed a string with timezone, timezone is kept (cached)');

		// test with DST
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 365,
			'dtstart' => date_create('2015-07-01 09:00:00', new DateTimeZone('Europe/Helsinki'))
		));
		$this->assertTrue($rrule->occursAt(date_create('2015-07-02 09:00:00',new DateTimeZone('Europe/Helsinki'))));
		$this->assertTrue($rrule->occursAt(date_create('2015-07-02 06:00:00',new DateTimeZone('UTC'))), 'During summer time, Europe/Helsinki is UTC+3 (cached)');
		$rrule->clearCache();
		$this->assertTrue($rrule->occursAt(date_create('2015-07-02 06:00:00',new DateTimeZone('UTC'))), 'During summer time, Europe/Helsinki is UTC+3 (uncached)');

		$this->assertTrue($rrule->occursAt(date_create('2015-12-02 09:00:00',new DateTimeZone('Europe/Helsinki'))));
		$this->assertTrue($rrule->occursAt(date_create('2015-12-02 07:00:00',new DateTimeZone('UTC'))), 'During winter time, Europe/Helsinki is UTC+2 (cached)');
		$rrule->clearCache();
		$this->assertTrue($rrule->occursAt(date_create('2015-12-02 07:00:00',new DateTimeZone('UTC'))), 'During winter time, Europe/Helsinki is UTC+2 (uncached)');
	}

	public function rulesWithMismatchedTimezones()
	{
		return array(
			array(
				array('DTSTART' => new DateTime('20160624Z'),'FREQ' => 'DAILY','INTERVAL' => 1,'UNTIL' => '20160628'),
				array(
					date_create('20160624Z'),
					date_create('20160625Z'),
					date_create('20160626Z'),
					date_create('20160627Z'),
					// date_create('20160628Z') // will not return this due to timezone mismatch (unless default timezone is utc)
				)
			),
			array(
				array('DTSTART' => new DateTime('20160624Z'),'FREQ' => 'DAILY','INTERVAL' => 1,'UNTIL' => '28-06-2016'),
				array(
					date_create('20160624Z'),
					date_create('20160625Z'),
					date_create('20160626Z'),
					date_create('20160627Z'),
					// date_create('20160628Z')  // will not return this due to timezone mismatch (unless default timezone is utc)
				)
			),
			array(
				array('DTSTART' => new DateTime('20160624Z'),'FREQ' => 'DAILY','INTERVAL' => 1,'UNTIL' => new DateTime('20160628', new DateTimeZone('Europe/Paris'))),
				array(
					date_create('20160624Z'),
					date_create('20160625Z'),
					date_create('20160626Z'),
					date_create('20160627Z'),
					// date_create('20160628Z') // will not return this due to timezone mismatch (unless default timezone is utc)
				)
			)
		);
	}

	/**
	 * Test bug issue #13
	 * @see https://github.com/rlanvin/php-rrule/issues/13
	 * @dataProvider rulesWithMismatchedTimezones
	 */
	public function testRulesWithMismatchedTimezones($rule, $occurrences)
	{
		$rrule = new RRule($rule);
		$this->assertEquals($occurrences, $rrule->getOccurrences(), 'Mismatched timezones makes for strange results');
	}

///////////////////////////////////////////////////////////////////////////////
// Other tests

	public function invalidConstructorParameters()
	{
		return [
			[new stdClass, null],
			[true, null],
			[1, null],
			[4.2, null],
			'dtstart optional parameter only for string rules' => [['FREQ' => 'DAILY'], new DateTime()]
		];
	}

	/**
	 * @dataProvider invalidConstructorParameters
	 */
	public function testConstructorDoesntAcceptInvalidTypes($parts, $dtstart)
	{
		$this->expectException(\InvalidArgumentException::class);
		new RRule($dtstart, $dtstart);
	}

	public function testIsFinite()
	{
		$rrule = new RRule(array(
			'freq' => 'yearly'
		));
		$this->assertTrue($rrule->isInfinite());
		$this->assertFalse($rrule->isFinite());

		$rrule = new RRule(array(
			'freq' => 'yearly',
			'count' => 10
		));
		$this->assertFalse($rrule->isInfinite());
		$this->assertTrue($rrule->isFinite());
	}

	public function testIsLeapYear()
	{
		$this->assertFalse(\RRule\is_leap_year(1700));
		$this->assertFalse(\RRule\is_leap_year(1800));
		$this->assertFalse(\RRule\is_leap_year(1900));
		$this->assertTrue(\RRule\is_leap_year(2000));
	}

	public function testDateTimeMutableReferenceBug()
	{
		$date = date_create('2007-01-01');
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 10,
			'dtstart' => $date
		));
		$this->assertEquals(date_create('2007-01-01'), $rrule[0]);
		$date->modify('+1day');
		$rrule->clearCache();
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible of dtstart');

		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 10,
			'dtstart' => '2007-01-01'
		));

		// offsetGet
		$this->assertEquals(date_create('2007-01-01'), $rrule[0]);
		$rrule[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with offsetGet (uncached)');
		$rrule[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with offsetGet (cached)');

		// iterate
		$rrule->clearCache();
		foreach ($rrule as $occurrence) {
			break;
		}
		$this->assertEquals(date_create('2007-01-01'), $occurrence);
		$occurrence->modify('+1 day');
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with foreach (uncached)');

		foreach ($rrule as $occurrence) {
			break;
		}
		$this->assertEquals(date_create('2007-01-01'), $occurrence);
		$occurrence->modify('+1 day');
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with foreach (cached)');

		// getOccurrences
		$occurrences = $rrule->getOccurrences();
		$this->assertEquals(date_create('2007-01-01'), $occurrences[0]);
		$occurrences[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-02'), $occurrences[0]);
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with getOccurrences (uncached version)');

		$occurrences = $rrule->getOccurrences();
		$this->assertEquals(date_create('2007-01-01'), $occurrences[0]);
		$occurrences[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-02'), $occurrences[0]);
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with getOccurrences (cached version)');

		// getOccurrencesBetween
		$occurrences = $rrule->getOccurrencesBetween(null, null);
		$this->assertEquals(date_create('2007-01-01'), $occurrences[0]);
		$occurrences[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-02'), $occurrences[0]);
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with getOccurrences (uncached version)');

		$occurrences = $rrule->getOccurrencesBetween(null, null);
		$this->assertEquals(date_create('2007-01-01'), $occurrences[0]);
		$occurrences[0]->modify('+1 day');
		$this->assertEquals(date_create('2007-01-02'), $occurrences[0]);
		$this->assertEquals(date_create('2007-01-01'), $rrule[0], 'No modification possible with getOccurrences (cached version)');
	}

	public function testGetRule()
	{
		$array = array(
			'FREQ' => 'YEARLY',
			'DTSTART' => '2016-01-01'
		);
		$rrule = new RRule($array);
		$this->assertInternalType('array', $rrule->getRule());
		$rule = $rrule->getRule();
		$this->assertEquals('YEARLY', $rule['FREQ']);
		$this->assertInternalType('string', $rule['DTSTART']);

		$rrule = new RRule("DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=HOURLY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1;BYHOUR=1");
		$rule = $rrule->getRule();
		$this->assertEquals('HOURLY', $rule['FREQ']);
		$this->assertTrue($rule['DTSTART'] instanceof \DateTime);

		$rrule = new RRule("DTSTART:19970901\nRRULE:FREQ=DAILY;UNTIL=19971224;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1");
		$rule = $rrule->getRule();
		$this->assertEquals('DAILY', $rule['FREQ']);
		$this->assertTrue($rule['DTSTART'] instanceof \DateTime);
	}

	/**
	 * Test Bug #90
	 * @see https://github.com/rlanvin/php-rrule/issues/90
	 */
	public function testDateImmutable()
	{
		$dtstart_immutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i', '2021-01-08 08:00');
		//$dtstart_mutable = \DateTime::createFromFormat('Y-m-d H:i', '2021-01-08 08:00');

		$rrule = new RRule([
			'BYDAY' => ['MO', 'WE', 'FR'],
			'FREQ' => 'WEEKLY',
			'WKST' => 'SU',
			'DTSTART' => $dtstart_immutable,
		]);

		$start = \DateTimeImmutable::createFromFormat('Y-m-d', '2020-01-01');
		$end = \DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-31');

		$occurrences = $rrule->getOccurrencesBetween($start, $end, 10);

		$this->assertEquals([
			new DateTime('Friday, January 8, 2021 08:00'),
			new DateTime('Monday, January 11, 2021 08:00'),
			new DateTime('Wednesday, January 13, 2021 08:00'),
			new DateTime('Friday, January 15, 2021 08:00'),
			new DateTime('Monday, January 18, 2021 08:00'),
			new DateTime('Wednesday, January 20, 2021 08:00'),
			new DateTime('Friday, January 22, 2021 08:00'),
			new DateTime('Monday, January 25, 2021 08:00'),
			new DateTime('Wednesday, January 27, 2021 08:00'),
			new DateTime('Friday, January 29, 2021 08:00')
		], $occurrences, 'DateTimeImmutable produces valid results');
	}

	/**
	 * Test bug #104
	 * @see https://github.com/rlanvin/php-rrule/issues/104
	 */
	public function testMicrosecondsAreRemovedFromInput()
	{
		$dtstart = '2022-04-22 12:00:00.5';
		$rule = new RRule([
			'dtstart' => $dtstart,
			'freq' => 'daily',
			'interval' => 1,
			'count' => 1
		]);
		$this->assertTrue($rule->occursAt('2022-04-22 12:00:00'));
		$this->assertTrue($rule->occursAt('2022-04-22 12:00:00.5'));
		$this->assertEquals(date_create('2022-04-22 12:00:00'), $rule[0]);
	}

///////////////////////////////////////////////////////////////////////////////
// Array access and countable interfaces

	public function testCountable()
	{
		$rrule = new RRule(array(
			'freq' => 'yearly',
			'count' => 10
		));
		$this->assertEquals(10, count($rrule));
	}

	public function testCannotCountInfinite()
	{
		$rrule = new RRule(array(
			'freq' => 'yearly'
		));
		$this->expectException(\LogicException::class);
		count($rrule);
	}

	public function testOffsetExists()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 3,
			'byday' => 'TU,TH',
			'dtstart' => '2007-01-01'
		));
		$this->assertTrue(isset($rrule[0]));
		$this->assertTrue(isset($rrule[1]));
		$this->assertTrue(isset($rrule[2]));
		$this->assertFalse(isset($rrule[3]));
	}

	public function testOffsetGet()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 3,
			'byday' => 'TU,TH',
			'dtstart' => '2007-01-01'
		));

		$this->assertEquals(date_create('2007-01-02'), $rrule[0]);
		$this->assertEquals(date_create('2007-01-02'), $rrule['0']);
		$this->assertEquals(date_create('2007-01-04'), $rrule[1]);
		$this->assertEquals(date_create('2007-01-04'), $rrule['1']);
		$this->assertEquals(date_create('2007-01-09'), $rrule[2]);
		$this->assertEquals(null, $rrule[4]);
		$this->assertEquals(null, $rrule['4']);
	}

	public function testOffsetSetUnsupported()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 3,
			'byday' => 'TU,TH',
			'dtstart' => '2007-01-01'
		));
		$this->expectException(\LogicException::class);
		$rrule[] = 'blah';
	}

	public function testOffsetUnsetUnsupported()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 3,
			'byday' => 'TU,TH',
			'dtstart' => '2007-01-01'
		));
		$this->expectException(\LogicException::class);
		unset($rrule[0]);
	}

	public function illegalOffsets()
	{
		return array(
			array('dtstart'),
			array('1dtstart'),
			array(array()),
			array(1.1),
			array(-1),
			array(null),
			array(new stdClass())
		);
	}

	/**
	 * @dataProvider illegalOffsets
	 */
	public function testOffsetGetInvalidArgument($offset)
	{
		$this->expectException(\InvalidArgumentException::class);
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 3,
			'byday' => 'TU,TH',
			'dtstart' => '2007-01-01'
		));
		$rrule[$offset];
	}

///////////////////////////////////////////////////////////////////////////////
// Human readable string conversion

	public function validLocales()
	{
		return array(
			// locale | result expected with intl | result expected without intl

			// 2 characters language code
			array('en', array('en'), array('en')),
			array('fr', array('fr'), array('fr')),
			array('sv', array('sv'), array('sv')),
			// with region and underscore
			array('en_US', array('en','en_US'), array('en','en_US')),
			array('en_US.utf-8', array('en','en_US'), array('en','en_US')),
            array('en_US_POSIX', array('en','en_US'), array('en','en_US')),
            array('sv_SE', array('sv','sv_SE'), array('sv','sv_SE')),
            // case insentitive
			array('en_sg', array('en','en_SG'), array('en','en_SG')),
			array('sv_se', array('sv','sv_SE'), array('sv','sv_SE')),
			// with a dash
			array('en-US', array('en','en_US'), array('en','en_US')),
			array('zh-Hant-TW', array('zh','zh_TW'), array('zh','zh_TW')), // real locale is zh-Hant-TW, but since we don't have a "zh" file, we just use "en" for the test
            array('sv-SE', array('sv','sv_SE'), array('sv','sv_SE')),

			// invalid
			array('eng', array('en'), false),
			array('invalid', array('invalid'), false),
			array('en_US._wr!ng', array('en','en_US'), false),
		);
	}

	/**
	 * Test that RRule::i18nLoad() does not throw an exception with valid locales.
	 *
	 * @dataProvider validLocales
	 */
	public function testI18nFilesToLoadWithIntl($locale, $files)
	{
		if (!extension_loaded('intl')) {
			$this->markTestSkipped('intl extension is not loaded');
		}

		$reflector = new ReflectionClass('RRule\RRule');
		$method = $reflector->getMethod('i18nFilesToLoad');
		$method->setAccessible(true);

		if (! $files) {
			try {
				$method->invokeArgs(null, array($locale, true));
				$this->fail('Expected InvalidArgumentException not thrown (files was '.json_encode($files).')');
			} catch (\InvalidArgumentException $e) {
			}
		}
		else {
			$this->assertEquals($files,$method->invokeArgs(null, array($locale, true)));
		}
	}

	/**
	 * @dataProvider validLocales
	 */
	public function testI18nFilesToLoadWithoutIntl($locale, $dummy, $files)
	{
		$reflector = new ReflectionClass('RRule\RRule');
		$method = $reflector->getMethod('i18nFilesToLoad');
		$method->setAccessible(true);

		if (! $files) {
			try {
				$method->invokeArgs(null, array($locale, false));
				$this->fail('Expected InvalidArgumentException not thrown (files was '.json_encode($files).')');
			} catch (\InvalidArgumentException $e) {
				$this->assertStringStartsWith("The locale option does not look like a valid locale:", $e->getMessage());
			}
		}
		else {
			$this->assertEquals($files, $method->invokeArgs(null, array($locale, false)));
		}
	}

	/**
	 * Locales for which we have a translation
	 */
	public function validTranslatedLocales()
	{
		return array(
			array('en'),
			array('en_US')
		);
	}

	/**
	 * Test that RRule::i18nLoad() does not throw an exception with valid locales.
	 *
	 * @dataProvider validTranslatedLocales
	 */
	public function testI18nLoadWithIntl($locale)
	{
		if (!extension_loaded('intl')) {
			$this->markTestSkipped('intl extension is not loaded');
		}

		$reflector = new ReflectionClass('RRule\RRule');
		$method = $reflector->getMethod('i18nLoad');
		$method->setAccessible(true);

		$result = $method->invokeArgs(null, array($locale, null, true));
		$this->assertNotEmpty($result);
	}

	/**
	 * Test that the RRule::i18nLoad() does not fail when provided with valid fallback locales
	 *
	 * @dataProvider validTranslatedLocales
	 */
	public function testI18nLoadFallback($fallback)
	{
		$reflector = new ReflectionClass('RRule\RRule');

		$method = $reflector->getMethod('i18nLoad');
		$method->setAccessible(true);

		$result = $method->invokeArgs(null, array('xx', $fallback));
		$this->assertNotEmpty($result);
	}

	/**
	 * Tests that the RRule::i18nLoad() fails as expected on invalid $locale settings
	 */
	public function testI18nLoadFailsWithoutIntl()
	{
		$this->expectException(\InvalidArgumentException::class);
		$reflector = new ReflectionClass('RRule\RRule');

		$method = $reflector->getMethod('i18nLoad');
		$method->setAccessible(true);
		$method->invokeArgs(null, array('invalid', 'en', false)); // even with a valid fallback it should fail
	}

	/**
	 * Tests that the RRule::i18nLoad() fails as expected on invalid $fallback settings
	 */
	public function testI18nLoadFallbackFailsWitoutIntl()
	{
		$this->expectException(\InvalidArgumentException::class);
		$reflector = new ReflectionClass('RRule\RRule');

		$method = $reflector->getMethod('i18nLoad');
		$method->setAccessible(true);
		$method->invokeArgs(null, array('xx', 'invalid', false));
	}

	public function testHumanReadableRuntimeException()
	{
		$this->expectException(\RuntimeException::class);
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 10,
			'dtstart' => '2007-01-01'
		));
		$rrule->humanReadable(array(
			'locale' => 'xx',
			'fallback' => 'xx'
		)); // the locales are correctly formatted, but not such file exist, so this should throw a RuntimeException
	}

	/**
	 * Test that humanReadable works
	 */
	public function testHumanReadableWithCLocale()
	{
		$rrule = new RRule(array(
			'freq' => 'daily',
			'count' => 10,
			'dtstart' => '2007-01-01'
		));

		setlocale(LC_MESSAGES, 'C');
		$this->assertNotEmpty($rrule->humanReadable(array('fallback' => null)), 'C locale is converted to "en"');
	}

	public function humanReadableStrings()
	{
		return array(
			array(
				"DTSTART:20170202T000000Z\nRRULE:FREQ=DAILY;UNTIL=20170205T000000Z",
				['locale' => "en"],
				"daily, starting from 2/2/17, until 2/5/17",
				'daily, starting from 2017-02-02 00:00:00, until 2017-02-05 00:00:00',
			),
			array(
				"RRULE:FREQ=DAILY;UNTIL=20190405T055959Z",
				['locale' => "en"],
				"daily, starting from 1/10/19, until 4/5/19",
				'daily, starting from 2019-01-10 12:00:00, until 2019-04-05 05:59:59',
				'2019-01-10T12:00:00-05:00'
			),
			array(
				"DTSTART:20170202T000000Z\nRRULE:FREQ=DAILY;UNTIL=20170205T000000Z",
				array('locale' => "en_IE"),
				"daily, starting from 02/02/2017, until 05/02/2017",
				'daily, starting from 2017-02-02 00:00:00, until 2017-02-05 00:00:00'
			),
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=DAILY;UNTIL=20170205T000000Z",
				array('locale' => "en_IE"),
				"daily, starting from 01/09/1997, until 04/02/2017",
				'daily, starting from 1997-09-01 09:00:00, until 2017-02-05 00:00:00'
			),
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=DAILY;UNTIL=20170205T000000Z",
				array('locale' => "en_IE", 'include_start' => false),
				"daily, until 04/02/2017",
				'daily, until 2017-02-05 00:00:00'
			),
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=DAILY",
				array('locale' => "en_IE", 'explicit_infinite' => false),
				"daily, starting from 01/09/1997",
				'daily, starting from 1997-09-01 09:00:00'
			),
			array(
				"DTSTART;TZID=America/New_York:19970901T090000\nRRULE:FREQ=YEARLY;INTERVAL=2",
				array('locale' => "en_IE", 'explicit_infinite' => false),
				"every 2 years, starting from 01/09/1997",
				'every 2 years, starting from 1997-09-01 09:00:00'
			),
			array(
				"FREQ=DAILY",
				array('locale' => "en_IE", 'include_start' => false, 'explicit_infinite' => false),
				"daily",
				"daily"
			),
			// with custom_path
			'custom_path' => array(
				"DTSTART:20170202T000000Z\nRRULE:FREQ=YEARLY;UNTIL=20170205T000000Z",
				array('locale' => "fr_BE", "custom_path" => __DIR__."/i18n"),
				"chaque année, à partir du 2/02/17, jusqu'au 5/02/17",
				"chaque année, à partir du 2017-02-02 00:00:00, jusqu'au 2017-02-05 00:00:00"
			),
			'custom_path cached separately' => array(
				"DTSTART:20170202T000000Z\nRRULE:FREQ=YEARLY;UNTIL=20170205T000000Z",
				array('locale' => "fr_BE"),
				"tous les ans, à partir du 2/02/17, jusqu'au 5/02/17",
				"tous les ans, à partir du 2017-02-02 00:00:00, jusqu'au 2017-02-05 00:00:00"
			),
			array(
				"RRULE:FREQ=DAILY;UNTIL=20190405T055959Z",
				array('locale' => "xx", "custom_path" => __DIR__."/i18n", "date_formatter" => function($date) { return "X"; }),
				"daily, starting from X, until X",
				"daily, starting from X, until X"
			),
		);
	}

	/**
	 * @dataProvider humanReadableStrings
	 */
	public function testHumanReadable($rrule, $options, $withIntl, $withoutIntl, $dtstart = null)
	{
		if ($dtstart) {
			$dtstart = new DateTime($dtstart);
		}
		$rrule = new RRule($rrule, $dtstart);
		$expected = extension_loaded('intl') ? $withIntl : $withoutIntl;
		$this->assertEquals($expected, $rrule->humanReadable($options));
	}
}
